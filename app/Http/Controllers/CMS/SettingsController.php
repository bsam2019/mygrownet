<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\CompanySettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private CompanySettingsService $settingsService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->cmsUser->company_id;
        $company   = $user->cmsUser->company;
        $settings  = $this->settingsService->getSettings($companyId);

        // Build signature URL if set
        $signatureUrl = null;
        if (!empty($company->settings['signature_image'])) {
            $signatureUrl = \Storage::disk('s3')->url($company->settings['signature_image']);
        }

        // Check if Material Planning module is enabled
        $hasMaterialPlanningModule = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', 'material-planning')
            ->where('status', 'active')
            ->exists();

        // Check if Construction Modules are enabled
        $hasConstructionModules = $company->settings['construction_modules'] ?? false;

        return Inertia::render('CMS/Settings/Index', [
            'company'      => array_merge($company->toArray(), [
                'has_material_planning_module' => $hasMaterialPlanningModule,
                'has_construction_modules' => $hasConstructionModules,
            ]),
            'settings'     => $settings,
            'signatureUrl' => $signatureUrl,
        ]);
    }

    public function updateBusinessHours(Request $request)
    {
        $validated = $request->validate([
            'business_hours' => 'required|array',
            'business_hours.*.open' => 'required|string',
            'business_hours.*.close' => 'required|string',
            'business_hours.*.enabled' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateBusinessHours($companyId, $validated['business_hours']);

        return back()->with('success', 'Business hours updated successfully');
    }

    public function updateTaxSettings(Request $request)
    {
        $validated = $request->validate([
            'tax.enabled' => 'required|boolean',
            'tax.default_rate' => 'required|numeric|min:0|max:100',
            'tax.tax_number' => 'nullable|string|max:100',
            'tax.tax_label' => 'required|string|max:50',
            'tax.inclusive' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateTaxSettings($companyId, $validated['tax']);

        return back()->with('success', 'Tax settings updated successfully');
    }

    public function updateApprovalThresholds(Request $request)
    {
        $validated = $request->validate([
            'approval_thresholds.expense_approval_required' => 'required|boolean',
            'approval_thresholds.expense_auto_approve_limit' => 'required|numeric|min:0',
            'approval_thresholds.quotation_approval_required' => 'required|boolean',
            'approval_thresholds.quotation_auto_approve_limit' => 'required|numeric|min:0',
            'approval_thresholds.payment_approval_required' => 'required|boolean',
            'approval_thresholds.payment_auto_approve_limit' => 'required|numeric|min:0',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateApprovalThresholds($companyId, $validated['approval_thresholds']);

        return back()->with('success', 'Approval thresholds updated successfully');
    }

    public function updateInvoiceSettings(Request $request)
    {
        $validated = $request->validate([
            'invoice.prefix' => 'required|string|max:10',
            'invoice.next_number' => 'required|integer|min:1',
            'invoice.due_days' => 'required|integer|min:1',
            'invoice.late_fee_enabled' => 'required|boolean',
            'invoice.late_fee_percentage' => 'required|numeric|min:0|max:100',
            'invoice.late_fee_days' => 'required|integer|min:1',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateInvoiceSettings($companyId, $validated['invoice']);

        return back()->with('success', 'Invoice settings updated successfully');
    }

    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'notifications.email_invoices' => 'required|boolean',
            'notifications.email_payments' => 'required|boolean',
            'notifications.email_quotations' => 'required|boolean',
            'notifications.email_low_stock' => 'required|boolean',
            'notifications.email_expense_approval' => 'required|boolean',
            'notifications.email_job_updates' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateNotificationSettings($companyId, $validated['notifications']);

        return back()->with('success', 'Notification settings updated successfully');
    }

    public function resetToDefaults(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->resetToDefaults($companyId);

        return back()->with('success', 'Settings reset to defaults');
    }

    public function updatePaymentInstructions(Request $request)
    {
        $validated = $request->validate([
            'payment_instructions.bank_name' => 'nullable|string|max:255',
            'payment_instructions.account_name' => 'nullable|string|max:255',
            'payment_instructions.account_number' => 'nullable|string|max:100',
            'payment_instructions.branch' => 'nullable|string|max:255',
            'payment_instructions.swift_code' => 'nullable|string|max:50',
            'payment_instructions.mobile_money_name' => 'nullable|string|max:100',
            'payment_instructions.mobile_money_number' => 'nullable|string|max:50',
            'payment_instructions.additional_instructions' => 'nullable|string|max:1000',
            'payment_instructions.show_on_invoice' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updatePaymentInstructions($companyId, $validated['payment_instructions']);

        return back()->with('success', 'Payment instructions updated successfully');
    }

    public function updateBrandingSettings(Request $request)
    {
        $validated = $request->validate([
            'branding.primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'branding.secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'branding.invoice_footer' => 'nullable|string|max:500',
            'branding.show_logo_on_invoice' => 'required|boolean',
            'branding.show_logo_on_receipt' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateBrandingSettings($companyId, $validated['branding']);

        return back()->with('success', 'Branding settings updated successfully');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $path = $this->settingsService->uploadLogo($companyId, $request->file('logo'));

        return back()->with('success', 'Logo uploaded successfully');
    }

    public function deleteLogo(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->deleteLogo($companyId);

        return back()->with('success', 'Logo deleted successfully');
    }

    public function updateSmsSettings(Request $request)
    {
        $validated = $request->validate([
            'sms.enabled' => 'required|boolean',
            'sms.provider' => 'required|in:africas_talking,twilio',
            'sms.api_key' => 'nullable|string|max:255',
            'sms.api_secret' => 'nullable|string|max:255',
            'sms.sender_id' => 'nullable|string|max:50',
            'sms.send_invoice_notifications' => 'required|boolean',
            'sms.send_payment_confirmations' => 'required|boolean',
            'sms.send_payment_reminders' => 'required|boolean',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        
        $this->settingsService->updateSmsSettings($companyId, $validated['sms']);

        return back()->with('success', 'SMS settings updated successfully');
    }

    public function updateDocumentDefaults(Request $request)
    {
        $validated = $request->validate([
            'document_defaults.quotation.notes' => 'nullable|string|max:2000',
            'document_defaults.quotation.terms' => 'nullable|string|max:2000',
            'document_defaults.invoice.notes'   => 'nullable|string|max:2000',
            'document_defaults.invoice.terms'   => 'nullable|string|max:2000',
            'document_defaults.receipt.notes'   => 'nullable|string|max:2000',
            'document_defaults.receipt.terms'   => 'nullable|string|max:2000',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $this->settingsService->updateDocumentDefaults($companyId, $validated['document_defaults'] ?? []);

        return back()->with('success', 'Document defaults updated successfully');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $this->settingsService->uploadSignature($companyId, $request->file('signature'));

        return back()->with('success', 'Signature uploaded successfully');
    }

    public function deleteSignature(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $this->settingsService->deleteSignature($companyId);

        return back()->with('success', 'Signature deleted successfully');
    }

    /**
     * Toggle the fabrication/aluminium module on or off for this company.
     */
    public function toggleFabricationModule(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $company = $request->user()->cmsUser->company;
        $company->setFabricationModule($validated['enabled']);

        $status = $validated['enabled'] ? 'enabled' : 'disabled';
        return back()->with('success', "Fabrication module {$status} successfully.");
    }

    /**
     * Toggle the BizDocs module on or off for this company.
     */
    public function toggleBizDocsModule(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'features' => 'nullable|array',
            'features.invoices' => 'nullable|boolean',
            'features.quotations' => 'nullable|boolean',
            'features.receipts' => 'nullable|boolean',
            'features.stationery' => 'nullable|boolean',
        ]);

        $company = $request->user()->cmsUser->company;
        
        // Update module status
        $settings = $company->settings ?? [];
        $settings['bizdocs_module'] = $validated['enabled'];
        
        // Update features if provided
        if (isset($validated['features'])) {
            $settings['bizdocs_features'] = $validated['features'];
        }
        
        $company->settings = $settings;
        $company->save();

        $status = $validated['enabled'] ? 'enabled' : 'disabled';
        return back()->with('success', "BizDocs module {$status} successfully.");
    }

    /**
     * Toggle the Material Planning module on or off for this company.
     */
    public function toggleMaterialPlanningModule(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $company = $user->cmsUser->company;
        
        // Check if module exists
        $module = DB::table('modules')->where('id', 'material-planning')->first();
        
        if (!$module) {
            return back()->with('error', 'Material Planning module not found.');
        }

        if ($validated['enabled']) {
            // Check if already exists
            $existing = DB::table('module_subscriptions')
                ->where('user_id', $user->id)
                ->where('module_id', 'material-planning')
                ->first();

            if ($existing) {
                // Update existing subscription
                DB::table('module_subscriptions')
                    ->where('user_id', $user->id)
                    ->where('module_id', 'material-planning')
                    ->update([
                        'status' => 'active',
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new subscription
                DB::table('module_subscriptions')->insert([
                    'user_id' => $user->id,
                    'module_id' => 'material-planning',
                    'subscription_tier' => 'free',
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => null,
                    'auto_renew' => false,
                    'billing_cycle' => 'monthly',
                    'amount' => 0.00,
                    'currency' => 'ZMW',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            return back()->with('success', 'Material Planning module enabled successfully.');
        } else {
            // Disable the module
            DB::table('module_subscriptions')
                ->where('user_id', $user->id)
                ->where('module_id', 'material-planning')
                ->delete();
            
            return back()->with('success', 'Material Planning module disabled successfully.');
        }
    }

    /**
     * Toggle the Construction Modules on or off for this company.
     */
    public function toggleConstructionModules(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $company = $request->user()->cmsUser->company;
        
        // Update module status
        $settings = $company->settings ?? [];
        $settings['construction_modules'] = $validated['enabled'];
        
        $company->settings = $settings;
        $company->save();

        $status = $validated['enabled'] ? 'enabled' : 'disabled';
        return back()->with('success', "Construction modules {$status} successfully.");
    }
}
