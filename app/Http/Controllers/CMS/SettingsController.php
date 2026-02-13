<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\CompanySettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(
        private CompanySettingsService $settingsService
    ) {}

    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $company = $request->user()->cmsUser->company;
        
        $settings = $this->settingsService->getSettings($companyId);

        return Inertia::render('CMS/Settings/Index', [
            'company' => $company,
            'settings' => $settings,
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
}
