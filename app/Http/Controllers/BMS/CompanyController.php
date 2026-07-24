<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Domain\BMS\Repositories\CompanyRepositoryInterface;
use App\Domain\BMS\Repositories\CmsUserRepositoryInterface;
use App\Domain\BMS\Repositories\RoleRepositoryInterface;
use App\Domain\BMS\Core\Services\IndustryPresetService;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\BMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\BMS\IndustryPresetModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepo,
        private CmsUserRepositoryInterface $cmsUserRepo,
        private RoleRepositoryInterface $roleRepo,
        private IndustryPresetService $presetService
    ) {}

    public function hub(Request $request): Response
    {
        $user = $request->user();
        $memberships = $user->cmsUsers()
            ->with('company', 'role')
            ->where('status', 'active')
            ->get();

        $companyIds = $memberships->pluck('company_id')->toArray();

        $activeJobs = JobModel::whereIn('company_id', $companyIds)
            ->whereIn('status', ['pending', 'in_progress'])
            ->groupBy('company_id')
            ->selectRaw('company_id, count(*) as count')
            ->pluck('count', 'company_id');

        $pendingInvoices = InvoiceModel::whereIn('company_id', $companyIds)
            ->whereIn('status', ['sent', 'partial'])
            ->groupBy('company_id')
            ->selectRaw('company_id, count(*) as count')
            ->pluck('count', 'company_id');

        $outstanding = InvoiceModel::whereIn('company_id', $companyIds)
            ->where('status', '!=', 'paid')
            ->groupBy('company_id')
            ->selectRaw('company_id, coalesce(sum(amount_due), 0) as total')
            ->pluck('total', 'company_id');

        $monthlyRevenue = JobModel::whereIn('company_id', $companyIds)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->groupBy('company_id')
            ->selectRaw('company_id, coalesce(sum(actual_value), 0) as total')
            ->pluck('total', 'company_id');

        $customerCount = CustomerModel::whereIn('company_id', $companyIds)
            ->where('status', 'active')
            ->groupBy('company_id')
            ->selectRaw('company_id, count(*) as count')
            ->pluck('count', 'company_id');

        $companies = $memberships->map(fn ($cu) => [
            'company_id'   => $cu->company_id,
            'company_name' => $cu->company->name,
            'industry'     => $cu->company->industry_type,
            'logo'         => $cu->company->logo_path,
            'role'         => $cu->role?->name,
            'status'       => $cu->company->status,
            'metrics'      => [
                'active_jobs'      => $activeJobs->get($cu->company_id, 0),
                'pending_invoices' => $pendingInvoices->get($cu->company_id, 0),
                'outstanding'      => (float) $outstanding->get($cu->company_id, 0),
                'monthly_revenue'  => (float) $monthlyRevenue->get($cu->company_id, 0),
                'total_customers'  => $customerCount->get($cu->company_id, 0),
            ],
        ]);

        return Inertia::render('BMS/Companies/Hub', [
            'companies' => $companies,
            'defaultCompanyId' => $user->default_company_id,
        ]);
    }

    public function create(Request $request): Response
    {
        $presets = IndustryPresetModel::active()->ordered()->get([
            'id', 'code', 'name', 'description', 'icon', 'sort_order',
        ]);

        return Inertia::render('BMS/Companies/Create', [
            'presets' => $presets,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'preset_code'   => 'nullable|string|exists:cms_industry_presets,code',
            'phone'         => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $companyId = DB::transaction(function () use ($validated, $user) {
            $company = CompanyModel::create([
                'name'          => $validated['name'],
                'industry_type' => $validated['industry_type'] ?? null,
                'phone'         => $validated['phone'] ?? null,
                'email'         => $validated['email'] ?? null,
                'address'       => $validated['address'] ?? null,
                'city'          => $validated['city'] ?? 'Lusaka',
                'country'       => $validated['country'] ?? 'Zambia',
                'status'        => 'active',
                'subscription_type' => 'complimentary',
                'complimentary_until' => now()->addDays(14),
                'settings' => [
                    'currency'     => 'ZMW',
                    'vat_enabled'  => true,
                    'vat_rate'     => 16,
                    'invoice_due_days' => 30,
                ],
            ]);

            $ownerRole = RoleModel::create([
                'company_id'     => $company->id,
                'name'           => 'Owner',
                'is_system_role' => true,
                'permissions'    => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ]);

            CmsUserModel::create([
                'company_id' => $company->id,
                'user_id'    => $user->id,
                'role_id'    => $ownerRole->id,
                'status'     => 'active',
            ]);

            if (!empty($validated['preset_code'])) {
                $this->presetService->applyPresetToCompany($company->id, $validated['preset_code']);
            }

            $this->setDefaultBizDocsTemplates($company);

            return $company->id;
        });

        session([
            'active_cms_company_id' => $companyId,
            'cms_company_id' => $companyId,
        ]);

        $request->session()->save();

        return redirect()->route('bms.dashboard')
            ->with('success', "Welcome to {$validated['name']}! Your company is ready.");
    }

    public function enter(Request $request, int $companyId)
    {
        $user = $request->user();

        $membership = $user->cmsUsers()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            abort(403, 'You do not have access to this company.');
        }

        session(['active_cms_company_id' => $companyId]);

        return redirect()->route('bms.dashboard');
    }

    public function setDefault(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $companyId = $validated['company_id'] ?? null;

        if ($companyId) {
            $membership = $user->cmsUsers()
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->first();

            if (!$membership) {
                return back()->withErrors(['error' => 'Company not found.']);
            }

            $user->update(['default_company_id' => $companyId]);
        } else {
            $user->update(['default_company_id' => null]);
        }

        return back()->with('success', $companyId ? 'Default company updated.' : 'Default company cleared.');
    }

    private function setDefaultBizDocsTemplates(CompanyModel $company): void
    {
        $defaultTemplates = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::where('is_default', true)
            ->whereIn('document_type', ['invoice', 'quotation', 'receipt'])
            ->get()
            ->keyBy('document_type');

        $settings = $company->settings ?? [];
        $settings['bizdocs_template_preferences'] = [];

        foreach (['invoice', 'quotation', 'receipt'] as $docType) {
            if (isset($defaultTemplates[$docType])) {
                $settings['bizdocs_template_preferences'][$docType] = $defaultTemplates[$docType]->id;
            }
        }

        $company->update(['settings' => $settings]);
    }
}
