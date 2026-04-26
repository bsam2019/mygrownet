<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyModel extends Model{
    protected $table = 'cms_companies';

    protected $fillable = [
        'name',
        'industry_type',
        'business_registration_number',
        'tax_number',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
        'invoice_footer',
        'status',
        'subscription_type',
        'sponsor_reference',
        'subscription_notes',
        'complimentary_until',
        'settings',
        'onboarding_completed',
        'onboarding_progress',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_progress' => 'array',
        'onboarding_completed_at' => 'datetime',
        'complimentary_until' => 'datetime',
    ];

    protected $appends = ['logo_url', 'has_bizdocs_module', 'bizdocs_features'];

    public function users(): HasMany
    {
        return $this->hasMany(CmsUserModel::class, 'company_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(RoleModel::class, 'company_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(CustomerModel::class, 'company_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobModel::class, 'company_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Generate public URL for company logo
     * For S3 files, returns the public URL
     * For old local files, returns the storage URL
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        // If it's an old local file path
        if (str_starts_with($this->logo_path, 'cms/logos/')) {
            return asset('storage/' . $this->logo_path);
        }

        // Otherwise, it's an S3 key - generate public URL
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->logo_path);
        } catch (\Exception $e) {
            \Log::error('Failed to generate URL for company logo', [
                'company_id' => $this->id,
                's3_key' => $this->logo_path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if the fabrication/aluminium module is enabled for this company.
     * Enabled when pricing rules exist OR explicitly toggled on in settings.
     */
    public function hasFabricationModule(): bool
    {
        // Explicit toggle in settings takes precedence
        if (isset($this->settings['fabrication_module'])) {
            return (bool) $this->settings['fabrication_module'];
        }

        // Auto-detect: enabled if pricing rules have been configured
        return PricingRulesModel::where('company_id', $this->id)->exists();
    }

    /**
     * Enable or disable the fabrication module for this company.
     */
    public function setFabricationModule(bool $enabled): void
    {
        $settings = $this->settings ?? [];
        $settings['fabrication_module'] = $enabled;
        $this->update(['settings' => $settings]);
    }

    /**
     * Check if BizDocs module is enabled for this company.
     * Enabled by default, can be disabled in settings.
     */
    public function hasBizDocsModule(): bool
    {
        // Explicit toggle in settings takes precedence
        if (isset($this->settings['bizdocs_module'])) {
            return (bool) $this->settings['bizdocs_module'];
        }

        // Default: enabled for all companies
        return true;
    }

    /**
     * Get BizDocs module accessor for frontend
     */
    public function getHasBizDocsModuleAttribute(): bool
    {
        return $this->hasBizDocsModule();
    }

    /**
     * Get BizDocs features configuration
     */
    public function getBizDocsFeatures(): array
    {
        return $this->settings['bizdocs_features'] ?? [
            'pdf_generation' => true,
            'print_stationery' => true,
            'email_documents' => true,
            'whatsapp_sharing' => false,
            'qr_codes' => false,
        ];
    }

    /**
     * Get BizDocs features accessor for frontend
     */
    public function getBizDocsFeaturesAttribute(): array
    {
        return $this->getBizDocsFeatures();
    }

    /**
     * Enable or disable the BizDocs module for this company.
     */
    public function setBizDocsModule(bool $enabled): void
    {
        $settings = $this->settings ?? [];
        $settings['bizdocs_module'] = $enabled;
        $this->update(['settings' => $settings]);
    }

    /**
     * Update BizDocs features configuration
     */
    public function setBizDocsFeatures(array $features): void
    {
        $settings = $this->settings ?? [];
        $settings['bizdocs_features'] = array_merge(
            $this->getBizDocsFeatures(),
            $features
        );
        $this->update(['settings' => $settings]);
    }

    /**
     * Get BizDocs template preference for a document type
     */
    public function getBizDocsTemplateId(string $documentType): ?int
    {
        return $this->settings['bizdocs_template_preferences'][$documentType] ?? null;
    }

    /**
     * Set BizDocs template preference for a document type
     */
    public function setBizDocsTemplateId(string $documentType, int $templateId): void
    {
        $settings = $this->settings ?? [];
        $settings['bizdocs_template_preferences'][$documentType] = $templateId;
        $this->update(['settings' => $settings]);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if company has valid access (subscription or complimentary)
     */
    public function hasValidAccess(): bool
    {
        // Company must be active
        if (!$this->isActive()) {
            return false;
        }

        // Check based on subscription type
        switch ($this->subscription_type) {
            case 'paid':
                // For paid subscriptions, you'd check payment status here
                // For now, just check if active
                return true;

            case 'sponsored':
            case 'partner':
                // Sponsored and partner accounts have access as long as they're active
                return true;

            case 'complimentary':
                // Check if complimentary access hasn't expired
                if ($this->complimentary_until) {
                    return now()->lte($this->complimentary_until);
                }
                // No expiration date means unlimited complimentary access
                return true;

            default:
                // Default to paid logic
                return true;
        }
    }

    /**
     * Check if complimentary access is expiring soon (within 7 days)
     */
    public function isComplimentaryExpiringSoon(): bool
    {
        if ($this->subscription_type !== 'complimentary' || !$this->complimentary_until) {
            return false;
        }

        return now()->diffInDays($this->complimentary_until, false) <= 7 
            && now()->lte($this->complimentary_until);
    }

    /**
     * Get days until complimentary access expires
     */
    public function daysUntilComplimentaryExpires(): ?int
    {
        if ($this->subscription_type !== 'complimentary' || !$this->complimentary_until) {
            return null;
        }

        return now()->diffInDays($this->complimentary_until, false);
    }
}
