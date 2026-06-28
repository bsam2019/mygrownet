<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceWhiteLabelModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WhiteLabelService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get white-label settings for a business
     */
    public function getSettings(int $businessId): ?array
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        if (!$settings) {
            return null;
        }

        return [
            'id' => $settings->id,
            'company_name' => $settings->company_name,
            'logo_url' => $settings->getLogoUrl(),
            'favicon_url' => $settings->getFaviconUrl(),
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'accent_color' => $settings->accent_color,
            'custom_domain' => $settings->custom_domain,
            'hide_powered_by' => $settings->hide_powered_by,
            'custom_css' => $settings->custom_css,
            'email_branding' => $settings->email_branding,
        ];
    }

    /**
     * Check if user can use white-label features
     */
    public function canUseWhiteLabel(User $user): array
    {
        if (!$this->subscriptionService->canPerformAction($user, 'white_label')) {
            return [
                'allowed' => false,
                'reason' => 'White-label features are available on Business plan. Please upgrade.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Create or update white-label settings
     */
    public function saveSettings(int $businessId, array $data): GrowFinanceWhiteLabelModel
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        $updateData = [
            'company_name' => $data['company_name'] ?? null,
            'primary_color' => $data['primary_color'] ?? GrowFinanceWhiteLabelModel::DEFAULT_PRIMARY_COLOR,
            'secondary_color' => $data['secondary_color'] ?? GrowFinanceWhiteLabelModel::DEFAULT_SECONDARY_COLOR,
            'accent_color' => $data['accent_color'] ?? GrowFinanceWhiteLabelModel::DEFAULT_ACCENT_COLOR,
            'custom_domain' => $data['custom_domain'] ?? null,
            'hide_powered_by' => $data['hide_powered_by'] ?? false,
            'custom_css' => $data['custom_css'] ?? null,
            'email_branding' => $data['email_branding'] ?? null,
        ];

        if ($settings) {
            $settings->update($updateData);
            return $settings->fresh();
        }

        return GrowFinanceWhiteLabelModel::create([
            'business_id' => $businessId,
            ...$updateData,
        ]);
    }

    /**
     * Upload logo
     */
    public function uploadLogo(int $businessId, UploadedFile $file): array
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        // Delete old logo if exists
        if ($settings && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        // Store new logo
        $path = $file->store("growfinance/{$businessId}/branding", 'public');

        if (!$settings) {
            $settings = GrowFinanceWhiteLabelModel::create([
                'business_id' => $businessId,
                'logo_path' => $path,
            ]);
        } else {
            $settings->update(['logo_path' => $path]);
        }

        return [
            'success' => true,
            'logo_url' => $settings->getLogoUrl(),
        ];
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(int $businessId, UploadedFile $file): array
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        // Delete old favicon if exists
        if ($settings && $settings->favicon_path) {
            Storage::disk('public')->delete($settings->favicon_path);
        }

        // Store new favicon
        $path = $file->store("growfinance/{$businessId}/branding", 'public');

        if (!$settings) {
            $settings = GrowFinanceWhiteLabelModel::create([
                'business_id' => $businessId,
                'favicon_path' => $path,
            ]);
        } else {
            $settings->update(['favicon_path' => $path]);
        }

        return [
            'success' => true,
            'favicon_url' => $settings->getFaviconUrl(),
        ];
    }

    /**
     * Remove logo
     */
    public function removeLogo(int $businessId): bool
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        if ($settings && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return true;
    }

    /**
     * Get CSS variables for white-label styling
     */
    public function getCssVariables(int $businessId): string
    {
        $settings = GrowFinanceWhiteLabelModel::forBusiness($businessId)->first();

        if (!$settings) {
            return '';
        }

        return $settings->getCssVariables();
    }

    /**
     * Validate custom domain
     */
    public function validateCustomDomain(string $domain): array
    {
        // Basic validation
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'valid' => false,
                'error' => 'Invalid domain format.',
            ];
        }

        // Check if domain is already in use
        $existing = GrowFinanceWhiteLabelModel::where('custom_domain', $domain)->first();
        if ($existing) {
            return [
                'valid' => false,
                'error' => 'This domain is already in use.',
            ];
        }

        return [
            'valid' => true,
            'dns_instructions' => [
                'type' => 'CNAME',
                'name' => $domain,
                'value' => 'app.mygrownet.com',
                'ttl' => 3600,
            ],
        ];
    }
}
