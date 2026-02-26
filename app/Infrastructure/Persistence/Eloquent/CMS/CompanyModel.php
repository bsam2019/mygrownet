<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyModel extends Model
{
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
