<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;

class CompanySettingsService
{
    private const DEFAULT_SETTINGS = [
        'business_hours' => [
            'monday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
            'tuesday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
            'wednesday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
            'thursday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
            'friday' => ['open' => '08:00', 'close' => '17:00', 'enabled' => true],
            'saturday' => ['open' => '09:00', 'close' => '13:00', 'enabled' => false],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'enabled' => false],
        ],
        'tax' => [
            'enabled' => true,
            'default_rate' => 16.0,
            'tax_number' => '',
            'tax_label' => 'VAT',
            'inclusive' => false,
        ],
        'approval_thresholds' => [
            'expense_approval_required' => true,
            'expense_auto_approve_limit' => 500,
            'quotation_approval_required' => false,
            'quotation_auto_approve_limit' => 5000,
            'payment_approval_required' => false,
            'payment_auto_approve_limit' => 10000,
        ],
        'invoice' => [
            'prefix' => 'INV',
            'next_number' => 1,
            'due_days' => 30,
            'late_fee_enabled' => false,
            'late_fee_percentage' => 5,
            'late_fee_days' => 7,
        ],
        'quotation' => [
            'prefix' => 'QUO',
            'next_number' => 1,
            'valid_days' => 30,
        ],
        'notifications' => [
            'email_invoices' => true,
            'email_payments' => true,
            'email_quotations' => true,
            'email_low_stock' => true,
            'email_expense_approval' => true,
            'email_job_updates' => true,
        ],
        'inventory' => [
            'track_stock' => true,
            'low_stock_threshold' => 10,
            'allow_negative_stock' => false,
        ],
        'payment_instructions' => [
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'branch' => '',
            'swift_code' => '',
            'mobile_money_name' => '',
            'mobile_money_number' => '',
            'additional_instructions' => '',
            'show_on_invoice' => true,
        ],
        'branding' => [
            'primary_color' => '#2563eb',
            'secondary_color' => '#64748b',
            'invoice_footer' => '',
            'show_logo_on_invoice' => true,
            'show_logo_on_receipt' => true,
        ],
        'sms' => [
            'enabled' => false,
            'provider' => 'africas_talking', // africas_talking or twilio
            'api_key' => '',
            'api_secret' => '',
            'sender_id' => '',
            'send_invoice_notifications' => false,
            'send_payment_confirmations' => false,
            'send_payment_reminders' => false,
        ],
    ];

    public function getSettings(int $companyId): array
    {
        $company = CompanyModel::findOrFail($companyId);
        $settings = $company->settings ?? [];

        // Merge with defaults to ensure all keys exist
        return $this->mergeWithDefaults($settings);
    }

    public function updateSettings(int $companyId, array $settings): CompanyModel
    {
        $company = CompanyModel::findOrFail($companyId);
        
        // Get current settings
        $currentSettings = $company->settings ?? [];
        
        // Merge new settings with current settings
        $updatedSettings = array_replace_recursive($currentSettings, $settings);
        
        $company->update(['settings' => $updatedSettings]);
        
        return $company->fresh();
    }

    public function updateBusinessHours(int $companyId, array $businessHours): CompanyModel
    {
        return $this->updateSettings($companyId, ['business_hours' => $businessHours]);
    }

    public function updateTaxSettings(int $companyId, array $taxSettings): CompanyModel
    {
        return $this->updateSettings($companyId, ['tax' => $taxSettings]);
    }

    public function updateApprovalThresholds(int $companyId, array $thresholds): CompanyModel
    {
        return $this->updateSettings($companyId, ['approval_thresholds' => $thresholds]);
    }

    public function updateInvoiceSettings(int $companyId, array $invoiceSettings): CompanyModel
    {
        return $this->updateSettings($companyId, ['invoice' => $invoiceSettings]);
    }

    public function updateNotificationSettings(int $companyId, array $notificationSettings): CompanyModel
    {
        return $this->updateSettings($companyId, ['notifications' => $notificationSettings]);
    }

    public function resetToDefaults(int $companyId): CompanyModel
    {
        $company = CompanyModel::findOrFail($companyId);
        $company->update(['settings' => self::DEFAULT_SETTINGS]);
        
        return $company->fresh();
    }

    private function mergeWithDefaults(array $settings): array
    {
        return array_replace_recursive(self::DEFAULT_SETTINGS, $settings);
    }

    public function getDefaultSettings(): array
    {
        return self::DEFAULT_SETTINGS;
    }

    public function isBusinessOpen(int $companyId, ?\DateTime $dateTime = null): bool
    {
        $dateTime = $dateTime ?? new \DateTime();
        $settings = $this->getSettings($companyId);
        
        $dayOfWeek = strtolower($dateTime->format('l'));
        $businessHours = $settings['business_hours'][$dayOfWeek] ?? null;
        
        if (!$businessHours || !$businessHours['enabled']) {
            return false;
        }
        
        $currentTime = $dateTime->format('H:i');
        return $currentTime >= $businessHours['open'] && $currentTime <= $businessHours['close'];
    }

    public function requiresApproval(int $companyId, string $type, float $amount): bool
    {
        $settings = $this->getSettings($companyId);
        $thresholds = $settings['approval_thresholds'];
        
        $requiresApprovalKey = "{$type}_approval_required";
        $autoApproveLimitKey = "{$type}_auto_approve_limit";
        
        if (!isset($thresholds[$requiresApprovalKey]) || !$thresholds[$requiresApprovalKey]) {
            return false;
        }
        
        $autoApproveLimit = $thresholds[$autoApproveLimitKey] ?? 0;
        return $amount > $autoApproveLimit;
    }

    public function updatePaymentInstructions(int $companyId, array $paymentInstructions): CompanyModel
    {
        return $this->updateSettings($companyId, ['payment_instructions' => $paymentInstructions]);
    }

    public function updateBrandingSettings(int $companyId, array $brandingSettings): CompanyModel
    {
        return $this->updateSettings($companyId, ['branding' => $brandingSettings]);
    }

    public function updateSmsSettings(int $companyId, array $smsSettings): CompanyModel
    {
        return $this->updateSettings($companyId, ['sms' => $smsSettings]);
    }

    public function uploadLogo(int $companyId, $file): string
    {
        $company = CompanyModel::findOrFail($companyId);
        
        // Delete old logo if exists
        if ($company->logo_path && \Storage::disk('public')->exists($company->logo_path)) {
            \Storage::disk('public')->delete($company->logo_path);
        }
        
        // Store new logo
        $path = $file->store('cms/logos', 'public');
        $company->update(['logo_path' => $path]);
        
        return $path;
    }

    public function deleteLogo(int $companyId): void
    {
        $company = CompanyModel::findOrFail($companyId);
        
        if ($company->logo_path && \Storage::disk('public')->exists($company->logo_path)) {
            \Storage::disk('public')->delete($company->logo_path);
        }
        
        $company->update(['logo_path' => null]);
    }

    public function getPaymentInstructions(int $companyId): array
    {
        $settings = $this->getSettings($companyId);
        return $settings['payment_instructions'] ?? self::DEFAULT_SETTINGS['payment_instructions'];
    }

    public function getBrandingSettings(int $companyId): array
    {
        $settings = $this->getSettings($companyId);
        return $settings['branding'] ?? self::DEFAULT_SETTINGS['branding'];
    }

    public function getSmsSettings(int $companyId): array
    {
        $settings = $this->getSettings($companyId);
        return $settings['sms'] ?? self::DEFAULT_SETTINGS['sms'];
    }
}
