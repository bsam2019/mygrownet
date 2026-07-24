<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\WhiteLabel;
use App\Domain\GrowFinance\Repositories\WhiteLabelRepositoryInterface;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WhiteLabelService
{
    private const DEFAULT_PRIMARY_COLOR = '#2563eb';
    private const DEFAULT_SECONDARY_COLOR = '#1e40af';
    private const DEFAULT_ACCENT_COLOR = '#059669';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private WhiteLabelRepositoryInterface $whiteLabelRepo,
    ) {}

    public function getSettings(int $businessId): ?array
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        if (!$settings) {
            return null;
        }

        return [
            'id' => $settings->id,
            'company_name' => $settings->companyName,
            'logo_url' => $this->getLogoUrl($settings),
            'favicon_url' => $this->getFaviconUrl($settings),
            'primary_color' => $settings->primaryColor,
            'secondary_color' => $settings->secondaryColor,
            'accent_color' => $settings->accentColor,
            'custom_domain' => $settings->customDomain,
            'hide_powered_by' => $settings->hidePoweredBy,
            'custom_css' => $settings->customCss,
            'email_branding' => $settings->emailBranding,
        ];
    }

    public function canUseWhiteLabel(int $userId): array
    {
        $user = User::findOrFail($userId);

        if (!$this->subscriptionService->canPerformAction($user, 'white_label')) {
            return [
                'allowed' => false,
                'reason' => 'White-label features are available on Business plan. Please upgrade.',
            ];
        }

        return ['allowed' => true];
    }

    public function saveSettings(int $businessId, array $data): array
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        $companyName = $data['company_name'] ?? $settings?->companyName;
        $primaryColor = $data['primary_color'] ?? $settings?->primaryColor ?? self::DEFAULT_PRIMARY_COLOR;
        $secondaryColor = $data['secondary_color'] ?? $settings?->secondaryColor ?? self::DEFAULT_SECONDARY_COLOR;
        $accentColor = $data['accent_color'] ?? $settings?->accentColor ?? self::DEFAULT_ACCENT_COLOR;
        $customDomain = $data['custom_domain'] ?? $settings?->customDomain;
        $hidePoweredBy = $data['hide_powered_by'] ?? $settings?->hidePoweredBy ?? false;
        $customCss = $data['custom_css'] ?? $settings?->customCss;
        $emailBranding = $data['email_branding'] ?? $settings?->emailBranding;

        if ($settings) {
            $updated = new WhiteLabel(
                id: $settings->id,
                businessId: $businessId,
                companyName: $companyName,
                logoPath: $settings->logoPath,
                faviconPath: $settings->faviconPath,
                primaryColor: $primaryColor,
                secondaryColor: $secondaryColor,
                accentColor: $accentColor,
                customDomain: $customDomain,
                hidePoweredBy: $hidePoweredBy,
                customCss: $customCss,
                emailBranding: $emailBranding,
                createdAt: $settings->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            return $this->whiteLabelRepo->save($updated)->toArray();
        }

        return $this->whiteLabelRepo->save(new WhiteLabel(
            id: null,
            businessId: $businessId,
            companyName: $companyName,
            logoPath: null,
            faviconPath: null,
            primaryColor: $primaryColor,
            secondaryColor: $secondaryColor,
            accentColor: $accentColor,
            customDomain: $customDomain,
            hidePoweredBy: $hidePoweredBy,
            customCss: $customCss,
            emailBranding: $emailBranding,
            createdAt: null,
            updatedAt: null,
        ))->toArray();
    }

    public function uploadLogo(int $businessId, UploadedFile $file): array
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        if ($settings && $settings->logoPath) {
            Storage::disk('public')->delete($settings->logoPath);
        }

        $path = $file->store("growfinance/{$businessId}/branding", 'public');

        if ($settings) {
            $updated = new WhiteLabel(
                id: $settings->id,
                businessId: $settings->businessId,
                companyName: $settings->companyName,
                logoPath: $path,
                faviconPath: $settings->faviconPath,
                primaryColor: $settings->primaryColor,
                secondaryColor: $settings->secondaryColor,
                accentColor: $settings->accentColor,
                customDomain: $settings->customDomain,
                hidePoweredBy: $settings->hidePoweredBy,
                customCss: $settings->customCss,
                emailBranding: $settings->emailBranding,
                createdAt: $settings->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $settings = $this->whiteLabelRepo->save($updated);
        } else {
            $settings = $this->whiteLabelRepo->save(new WhiteLabel(
                id: null,
                businessId: $businessId,
                logoPath: $path,
            ));
        }

        return [
            'success' => true,
            'logo_url' => $this->getLogoUrl($settings),
        ];
    }

    public function uploadFavicon(int $businessId, UploadedFile $file): array
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        if ($settings && $settings->faviconPath) {
            Storage::disk('public')->delete($settings->faviconPath);
        }

        $path = $file->store("growfinance/{$businessId}/branding", 'public');

        if ($settings) {
            $updated = new WhiteLabel(
                id: $settings->id,
                businessId: $settings->businessId,
                companyName: $settings->companyName,
                logoPath: $settings->logoPath,
                faviconPath: $path,
                primaryColor: $settings->primaryColor,
                secondaryColor: $settings->secondaryColor,
                accentColor: $settings->accentColor,
                customDomain: $settings->customDomain,
                hidePoweredBy: $settings->hidePoweredBy,
                customCss: $settings->customCss,
                emailBranding: $settings->emailBranding,
                createdAt: $settings->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $settings = $this->whiteLabelRepo->save($updated);
        } else {
            $settings = $this->whiteLabelRepo->save(new WhiteLabel(
                id: null,
                businessId: $businessId,
                faviconPath: $path,
            ));
        }

        return [
            'success' => true,
            'favicon_url' => $this->getFaviconUrl($settings),
        ];
    }

    public function removeLogo(int $businessId): bool
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        if ($settings && $settings->logoPath) {
            Storage::disk('public')->delete($settings->logoPath);

            $updated = new WhiteLabel(
                id: $settings->id,
                businessId: $settings->businessId,
                companyName: $settings->companyName,
                logoPath: null,
                faviconPath: $settings->faviconPath,
                primaryColor: $settings->primaryColor,
                secondaryColor: $settings->secondaryColor,
                accentColor: $settings->accentColor,
                customDomain: $settings->customDomain,
                hidePoweredBy: $settings->hidePoweredBy,
                customCss: $settings->customCss,
                emailBranding: $settings->emailBranding,
                createdAt: $settings->createdAt,
                updatedAt: new \DateTimeImmutable('now'),
            );
            $this->whiteLabelRepo->save($updated);
        }

        return true;
    }

    public function getCssVariables(int $businessId): string
    {
        $settings = $this->whiteLabelRepo->findByBusiness($businessId);

        if (!$settings) {
            return '';
        }

        return <<<CSS
:root {
    --gf-primary: {$settings->primaryColor};
    --gf-secondary: {$settings->secondaryColor};
    --gf-accent: {$settings->accentColor};
}
CSS;
    }

    public function validateCustomDomain(string $domain): array
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'valid' => false,
                'error' => 'Invalid domain format.',
            ];
        }

        $existing = DB::table('growfinance_white_label_settings')
            ->where('custom_domain', $domain)
            ->first();

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

    private function getLogoUrl(WhiteLabel $settings): ?string
    {
        if (!$settings->logoPath) {
            return null;
        }
        return asset('storage/' . $settings->logoPath);
    }

    private function getFaviconUrl(WhiteLabel $settings): ?string
    {
        if (!$settings->faviconPath) {
            return null;
        }
        return asset('storage/' . $settings->faviconPath);
    }
}
