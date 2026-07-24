<?php

namespace App\Http\Controllers\BMS;

use App\Domain\BMS\Core\Services\InvoiceService;
use App\Domain\BMS\Core\Services\PdfInvoiceService;
use App\Domain\BMS\Core\ValueObjects\InvoiceStatus;
use App\Domain\BMS\Repositories\InvoiceRepositoryInterface;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;
use App\Notifications\BMS\InvoiceSentNotification;
use App\Services\BMS\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly PdfInvoiceService $pdfService,
        private readonly EmailService $emailService,
        private readonly InvoiceRepositoryInterface $invoiceRepo,
        private readonly CustomerRepositoryInterface $customerRepo
    ) {}

    private function getCompanyId(Request $request): ?int
    {
        $cmsUser = CmsUserModel::where('user_id', $request->user()->id)->first();
        return $cmsUser?->company_id;
    }

    private function getBmsUserOrFail(Request $request)
    {
        $cmsUser = CmsUserModel::where('user_id', $request->user()->id)->first();
        if (!$cmsUser || !$cmsUser->company_id) {
            abort(403, 'You must be associated with a company.');
        }
        return $cmsUser;
    }

    public function index(Request $request): Response
    {
        $cmsUser = CmsUserModel::where('user_id', $request->user()->id)->first();

        if (!$cmsUser || !$cmsUser->company_id) {
            return Inertia::render('BMS/Invoices/Index', [
                'invoices' => ['data' => [], 'links' => [], 'meta' => []],
                'summary' => [
                    'total_invoices' => 0, 'draft_count' => 0, 'sent_count' => 0,
                    'partial_count' => 0, 'paid_count' => 0, 'total_value' => 0,
                    'total_paid' => 0, 'total_outstanding' => 0,
                ],
                'filters' => ['status' => 'all', 'search' => ''],
                'statuses' => InvoiceStatus::all(),
            ])->with('error', 'You must be associated with a company to view invoices.');
        }

        $companyId = $cmsUser->company_id;

        $query = InvoiceModel::where('company_id', $companyId)
            ->with(['customer', 'items', 'branch'])
            ->forBranch($request->branch_id);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        $summary = $this->invoiceRepo->getSummary($companyId);

        $branches = BranchModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->get(['id', 'branch_name']);

        return Inertia::render('BMS/Invoices/Index', [
            'invoices' => $invoices,
            'summary' => $summary,
            'filters' => [
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
                'branch_id' => $request->branch_id ?? '',
            ],
            'statuses' => InvoiceStatus::all(),
            'branches' => $branches,
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $this->getCompanyId($request);

        if (!$companyId) {
            return redirect()->route('bms.invoices.index')->with('error', 'You must be associated with a company.');
        }

        $customers = \App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'outstanding_balance']);

        return Inertia::render('BMS/Invoices/Create', [
            'customers'    => $customers,
            'defaultNotes' => app(\App\Domain\BMS\Core\Services\CompanySettingsService::class)->getDocumentDefaults($companyId, 'invoice')['notes'] ?? '',
            'defaultTerms' => app(\App\Domain\BMS\Core\Services\CompanySettingsService::class)->getDocumentDefaults($companyId, 'invoice')['terms'] ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'              => 'required|exists:cms_customers,id',
            'due_date'                 => 'nullable|date|after_or_equal:today',
            'notes'                    => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:500',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.dimensions'       => 'nullable|string|max:100',
            'items.*.dimensions_value' => 'nullable|numeric|min:0',
        ]);

        $cmsUser = $this->getBmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->createInvoice(
                companyId: $cmsUser->company_id,
                customerId: $validated['customer_id'],
                items: $validated['items'],
                dueDate: $validated['due_date'] ?? null,
                notes: $validated['notes'] ?? null,
                createdBy: $cmsUser->id
            );

            return redirect()
                ->route('bms.invoices.show', $invoice->id)
                ->with('success', 'Invoice created successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(Request $request, int $id): Response
    {
        $cmsUser = $this->getBmsUserOrFail($request);

        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'payments.allocations'])
            ->findOrFail($id);

        return Inertia::render('BMS/Invoices/Show', [
            'invoice' => $invoice,
            'statuses' => InvoiceStatus::all(),
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $cmsUser = $this->getBmsUserOrFail($request);
        $companyId = $cmsUser->company_id;

        $invoice = InvoiceModel::where('company_id', $companyId)
            ->with(['customer', 'items'])
            ->findOrFail($id);

        if ($invoice->status !== InvoiceStatus::DRAFT->value) {
            return redirect()
                ->route('bms.invoices.show', $id)
                ->with('error', 'Only draft invoices can be edited');
        }

        $customers = \App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('BMS/Invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'due_date'                 => 'nullable|date',
            'notes'                    => 'nullable|string|max:1000',
            'items'                    => 'required|array|min:1',
            'items.*.description'      => 'required|string|max:500',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.dimensions'       => 'nullable|string|max:100',
            'items.*.dimensions_value' => 'nullable|numeric|min:0',
        ]);

        $cmsUser = $this->getBmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->updateInvoice(
                invoiceId: $id,
                items: $validated['items'],
                dueDate: $validated['due_date'] ?? null,
                notes: $validated['notes'] ?? null,
                userId: $cmsUser->id
            );

            return redirect()
                ->route('bms.invoices.show', $invoice->id)
                ->with('success', 'Invoice updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function send(Request $request, int $id): RedirectResponse
    {
        $cmsUser = $this->getBmsUserOrFail($request);

        try {
            $invoice = $this->invoiceService->sendInvoice($id, $cmsUser->id);

            $eloquentInvoice = InvoiceModel::with(['customer', 'company', 'items'])->find($id);

            if ($eloquentInvoice && $eloquentInvoice->customer && $eloquentInvoice->customer->email) {
                $pdf = $this->pdfService->generate($eloquentInvoice);
                $pdfPath = storage_path("app/temp/invoice-{$eloquentInvoice->invoice_number}.pdf");

                if (!file_exists(storage_path('app/temp'))) {
                    mkdir(storage_path('app/temp'), 0755, true);
                }

                file_put_contents($pdfPath, $pdf->output());

                $emailSent = $this->emailService->sendEmail(
                    company: $eloquentInvoice->company,
                    to: $eloquentInvoice->customer->email,
                    subject: "Invoice {$eloquentInvoice->invoice_number} from {$eloquentInvoice->company->name}",
                    view: 'emails.cms.invoice-sent',
                    data: [
                        'invoice' => $eloquentInvoice,
                        'company' => $eloquentInvoice->company,
                        'customer' => $eloquentInvoice->customer,
                        'recipient_name' => $eloquentInvoice->customer->name,
                    ],
                    emailType: 'invoice',
                    referenceType: 'invoice',
                    referenceId: $eloquentInvoice->id,
                    attachmentPath: $pdfPath
                );

                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }

                if ($emailSent && $eloquentInvoice->customer->user) {
                    $eloquentInvoice->customer->user->notify(new InvoiceSentNotification([
                        'id' => $eloquentInvoice->id,
                        'invoice_number' => $eloquentInvoice->invoice_number,
                        'customer_name' => $eloquentInvoice->customer->name,
                        'total_amount' => $eloquentInvoice->total_amount,
                        'due_date' => $eloquentInvoice->due_date?->format('Y-m-d'),
                    ]));
                }
            }

            return back()->with('success', 'Invoice sent successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $cmsUser = $this->getBmsUserOrFail($request);

        try {
            $this->invoiceService->cancelInvoice($id, $validated['reason'], $cmsUser->id);
            return back()->with('success', 'Invoice cancelled');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel invoice: ' . $e->getMessage());
        }
    }

    public function void(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $cmsUser = $this->getBmsUserOrFail($request);

        try {
            $this->invoiceService->voidInvoice($id, $validated['reason'], $cmsUser->id);
            return back()->with('success', 'Invoice voided');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to void invoice: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Request $request, int $id)
    {
        $cmsUser = $this->getBmsUserOrFail($request);
        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'company'])
            ->findOrFail($id);

        if ($invoice->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateInvoicePdf($invoice);
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoice->invoice_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs invoice PDF generation failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return $this->pdfService->download($invoice);
    }

    public function previewPdf(Request $request, int $id)
    {
        $cmsUser = $this->getBmsUserOrFail($request);
        $invoice = InvoiceModel::where('company_id', $cmsUser->company_id)
            ->with(['customer', 'items', 'company'])
            ->findOrFail($id);

        if ($invoice->company->hasBizDocsModule()) {
            try {
                $adapter = app(\App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface::class);
                $pdfContent = $adapter->generateInvoicePdf($invoice);
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="invoice-' . $invoice->invoice_number . '.pdf"');
            } catch (\Exception $e) {
                \Log::error('BizDocs invoice PDF preview failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return $this->pdfService->stream($invoice);
    }
}
