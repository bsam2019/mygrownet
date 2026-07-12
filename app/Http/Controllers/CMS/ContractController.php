<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\ApprovalWorkflowService;
use App\Infrastructure\Persistence\Eloquent\CMS\ContractModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ContractTemplateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Services\CMS\EmailService;
use App\Services\ContractSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public function __construct(
        private ContractSignatureService $signatureService,
        private EmailService $emailService,
        private ApprovalWorkflowService $approvalService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $contracts = ContractModel::forCompany($companyId)
            ->with(['customer', 'template', 'createdBy.user', 'branch'])
            ->forBranch($request->branch_id)
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%")
                ->orWhere('contract_number', 'like', "%{$s}%"))
            ->when($request->status, fn($q) => $q->byStatus($request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $expiringCount = ContractModel::forCompany($companyId)->expiringSoon(30)->count();
        $overdueCount = ContractModel::forCompany($companyId)->overdue()->count();
        $activeCount = ContractModel::forCompany($companyId)->active()->count();

        $branches = \App\Infrastructure\Persistence\Eloquent\CMS\BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('CMS/Contracts/Index', [
            'contracts' => $contracts,
            'filters' => $request->only(['search', 'status', 'branch_id']),
            'stats' => [
                'active' => $activeCount,
                'expiring_soon' => $expiringCount,
                'overdue' => $overdueCount,
            ],
            'branches' => $branches,
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;

        $customers = CustomerModel::forCompany($companyId)->active()->orderBy('name')->get(['id', 'name', 'email']);
        $templates = ContractTemplateModel::where('company_id', $companyId)->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('CMS/Contracts/Create', [
            'customers' => $customers,
            'templates' => $templates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:cms_customers,id',
            'template_id' => 'nullable|exists:cms_contract_templates,id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'total_value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $lastContract = ContractModel::forCompany($companyId)->orderBy('id', 'desc')->first();
        $sequence = $lastContract ? ((int) substr($lastContract->contract_number, -4)) + 1 : 1;
        $contractNumber = 'CNT-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        ContractModel::create([
            ...$validated,
            'company_id' => $companyId,
            'contract_number' => $contractNumber,
            'status' => 'draft',
            'created_by' => $request->user()->cmsUser->id,
        ]);

        return redirect()->route('cms.contracts.index')->with('success', 'Contract created successfully.');
    }

    /**
     * Ensure the contract belongs to the authenticated user's company.
     */
    private function authorizeContract(Request $request, ContractModel $contract): void
    {
        $cmsUser = $request->user()->cmsUser;
        abort_unless($cmsUser, 403, 'No CMS access.');
        abort_if($contract->company_id !== $cmsUser->company_id, 403, 'Contract not found in your company.');
    }

    public function show(Request $request, ContractModel $contract): Response
    {
        $this->authorizeContract($request, $contract);

        $contract->load(['customer', 'template', 'createdBy.user', 'approvedBy.user', 'renewals.renewedContract', 'signatures']);

        $signedPdfUrl = app(ContractSignatureService::class)->getSignedPdfUrl($contract);

        return Inertia::render('CMS/Contracts/Show', [
            'contract' => $contract,
            'signedPdfUrl' => $signedPdfUrl,
        ]);
    }

    public function edit(Request $request, ContractModel $contract): Response
    {
        $this->authorizeContract($request, $contract);

        $companyId = $request->user()->cmsUser->company_id;

        $customers = CustomerModel::forCompany($companyId)->active()->orderBy('name')->get(['id', 'name']);
        $templates = ContractTemplateModel::where('company_id', $companyId)->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('CMS/Contracts/Edit', [
            'contract' => $contract,
            'customers' => $customers,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, ContractModel $contract): RedirectResponse
    {
        $this->authorizeContract($request, $contract);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:cms_customers,id',
            'template_id' => 'nullable|exists:cms_contract_templates,id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'total_value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,active,expired,terminated',
        ]);

        $contract->update($validated);

        return redirect()->route('cms.contracts.show', $contract->id)->with('success', 'Contract updated.');
    }

    public function activate(Request $request, ContractModel $contract): RedirectResponse
    {
        $this->authorizeContract($request, $contract);

        $user = $request->user()->cmsUser;
        $companyId = $user->company_id;

        if ($this->approvalService->requiresApproval($companyId, 'contract', $contract->total_value)) {
            try {
                $this->approvalService->createApprovalRequest(
                    $companyId,
                    'contract',
                    $contract->id,
                    $contract->total_value,
                    $user->id,
                    'Contract activation: ' . $contract->title
                );
                return back()->with('info', 'Contract activation requires approval. An approval request has been submitted.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to create approval request: ' . $e->getMessage());
            }
        }

        $contract->update([
            'status' => 'active',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Contract activated.');
    }

    public function terminate(Request $request, ContractModel $contract): RedirectResponse
    {
        $this->authorizeContract($request, $contract);

        $contract->update(['status' => 'terminated']);

        return back()->with('success', 'Contract terminated.');
    }

    public function sign(Request $request, ContractModel $contract): RedirectResponse
    {
        $this->authorizeContract($request, $contract);

        $validated = $request->validate([
            'party' => 'required|in:customer,company',
            'signature_data' => 'required|string',
            'signer_name' => 'nullable|string|max:255',
            'signer_email' => 'nullable|email|max:255',
        ]);

        $companyUser = $request->user()->cmsUser;
        $signerName = $validated['signer_name'] ?? $companyUser->name;
        $signerEmail = $validated['signer_email'] ?? $companyUser->user->email;

        $this->signatureService->sign(
            $contract,
            $validated['party'],
            $validated['signature_data'],
            $signerName,
            $signerEmail,
        );

        $message = $validated['party'] === 'customer'
            ? 'Customer signature captured.'
            : 'Company signature captured.';

        return back()->with('success', $message);
    }

    public function sendForSigning(Request $request, ContractModel $contract): RedirectResponse
    {
        $this->authorizeContract($request, $contract);

        $token = $this->signatureService->generateSigningToken($contract);

        $customer = $contract->customer;

        if (!$customer || !$customer->email) {
            return back()->with('error', 'Contract has no customer with an email address.');
        }

        $company = CompanyModel::find($contract->company_id);
        $signingUrl = route('public.contracts.sign', ['contract' => $contract->id, 'token' => $token]);

        $this->emailService->sendEmail(
            company: $company,
            to: $customer->email,
            subject: "Contract {$contract->contract_number} ready for signing - {$company->name}",
            view: 'emails.cms.contract-signing-request',
            data: [
                'contract' => $contract,
                'company' => $company,
                'customer' => $customer,
                'signingUrl' => $signingUrl,
                'recipient_name' => $customer->name,
            ],
            emailType: 'contract_signing',
            referenceType: 'contract',
            referenceId: $contract->id,
        );

        return back()->with('success', 'Signing link sent to ' . $customer->email);
    }

    public function showSigningPage(ContractModel $contract, string $token)
    {
        if ($contract->signing_token !== $token || $contract->signed_by_customer) {
            abort(404);
        }

        $contract->load(['customer', 'template']);

        return Inertia::render('CMS/Contracts/CustomerSign', [
            'contract' => $contract,
            'token' => $token,
        ]);
    }

    public function submitCustomerSignature(Request $request, ContractModel $contract, string $token): RedirectResponse
    {
        if ($contract->signing_token !== $token || $contract->signed_by_customer) {
            abort(404);
        }

        $validated = $request->validate([
            'signature_data' => 'required|string',
            'signer_name' => 'required|string|max:255',
        ]);

        $customer = $contract->customer;

        $signature = $this->signatureService->sign(
            $contract,
            'customer',
            $validated['signature_data'],
            $validated['signer_name'],
            $customer?->email,
        );

        $signature->update(['signing_token' => $token]);

        $contract->update(['signing_token' => null]);

        return redirect()->route('public.contracts.signed', $contract->id);
    }

    public function signedConfirmation(ContractModel $contract): Response
    {
        $contract->load('signatures');
        $signedPdfUrl = $this->signatureService->getSignedPdfUrl($contract);

        return Inertia::render('CMS/Contracts/SignedConfirmation', [
            'contract' => $contract,
            'signedPdfUrl' => $signedPdfUrl,
        ]);
    }

    public function downloadPdf(ContractModel $contract)
    {
        $path = $contract->signed_pdf_path;

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path, $contract->contract_number . '-signed.pdf');
    }

    public function renew(Request $request, ContractModel $contract): RedirectResponse
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'new_end_date' => 'required|date|after:' . $contract->start_date,
            'total_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $newContract = ContractModel::create([
            'company_id' => $companyId,
            'contract_number' => $contract->contract_number . '-R',
            'customer_id' => $contract->customer_id,
            'template_id' => $contract->template_id,
            'title' => $contract->title . ' (Renewed)',
            'description' => $contract->description,
            'start_date' => now(),
            'end_date' => $validated['new_end_date'],
            'total_value' => $validated['total_value'] ?? $contract->total_value,
            'currency' => $contract->currency,
            'terms' => $contract->terms,
            'status' => 'active',
            'created_by' => $request->user()->cmsUser->id,
        ]);

        $contract->update(['status' => 'renewed']);

        $contract->renewals()->create([
            'renewed_contract_id' => $newContract->id,
            'renewal_date' => now(),
            'status' => 'approved',
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->cmsUser->id,
        ]);

        return redirect()->route('cms.contracts.show', $newContract->id)->with('success', 'Contract renewed.');
    }
}
