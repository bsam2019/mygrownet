<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RoleModel;
use App\Infrastructure\Persistence\Eloquent\CMS\IndustryPresetModel;
use App\Domain\CMS\Core\Services\IndustryPresetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct(
        private IndustryPresetService $presetService
    ) {}

    /**
     * Company Hub — list all companies the user belongs to,
     * and offer to create a new one or join an existing one.
     */
    public function hub(Request $request): Response
    {
        $user = $request->user();

        $companies = $user->cmsUsers()
            ->with('company', 'role')
            ->where('status', 'active')
            ->get()
            ->map(fn ($cu) => [
                'company_id'   => $cu->company_id,
                'company_name' => $cu->company->name,
                'industry'     => $cu->company->industry_type,
                'logo'         => $cu->company->logo_path,
                'role'         => $cu->role?->name,
                'status'       => $cu->company->status,
            ]);

        return Inertia::render('CMS/Companies/Hub', [
            'companies' => $companies,
        ]);
    }

    /**
     * Show the create company form.
     */
    public function create(Request $request): Response
    {
        $presets = IndustryPresetModel::active()->ordered()->get([
            'id', 'code', 'name', 'description', 'icon', 'sort_order',
        ]);

        return Inertia::render('CMS/Companies/Create', [
            'presets' => $presets,
        ]);
    }

    /**
     * Store a new company and link the user as Owner.
     */
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

        DB::transaction(function () use ($validated, $user) {
            // Create company
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

            // Create Owner role
            $ownerRole = RoleModel::create([
                'company_id'     => $company->id,
                'name'           => 'Owner',
                'is_system_role' => true,
                'permissions'    => ['*'],
                'approval_authority' => ['limit' => 999999999],
            ]);

            // Link user as Owner
            CmsUserModel::create([
                'company_id' => $company->id,
                'user_id'    => $user->id,
                'role_id'    => $ownerRole->id,
                'status'     => 'active',
            ]);

            // Apply industry preset if chosen
            if (!empty($validated['preset_code'])) {
                $this->presetService->applyPresetToCompany($company->id, $validated['preset_code']);
            }

            // Set default BizDocs templates
            $this->setDefaultBizDocsTemplates($company);

            // Set as active company in session
            session(['active_cms_company_id' => $company->id]);
        });

        return redirect()->route('cms.dashboard')
            ->with('success', "Welcome to {$validated['name']}! Your company is ready.");
    }

    /**
     * Enter an existing company (set it as active).
     */
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

        return redirect()->route('cms.dashboard');
    }

    /**
     * Set default BizDocs templates for a company
     */
    private function setDefaultBizDocsTemplates(CompanyModel $company): void
    {
        // Get default templates for each document type
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
