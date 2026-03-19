<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_name',
        'slug',
        'owner_user_id',
        'subscription_plan_id',
        'business_email',
        'phone',
        'country',
        'currency',
        'timezone',
        'locale',
        'status',
        'trial_ends_at',
        'suspended_at',
        'onboarding_completed',
        'storage_limit_mb',
        'storage_used_mb',
        'site_limit',
        'sites_used',
        'team_member_limit',
        'allow_white_label',
        'referral_code',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'onboarding_completed' => 'boolean',
        'allow_white_label' => 'boolean',
    ];

    /**
     * Get the owner of the agency
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get the subscription plan
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get agency users (team members)
     */
    public function agencyUsers()
    {
        return $this->hasMany(AgencyUser::class);
    }

    /**
     * Get users through agency_users pivot
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'agency_users')
            ->withPivot('role_id', 'status', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Get agency clients
     */
    public function clients()
    {
        return $this->hasMany(AgencyClient::class);
    }

    /**
     * Get agency branding settings
     */
    public function branding()
    {
        return $this->hasOne(AgencyBrandingSetting::class);
    }

    /**
     * Check if agency has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->subscriptionPlan) {
            return false;
        }

        return $this->subscriptionPlan->hasFeature($feature);
    }

    /**
     * Get feature value
     */
    public function getFeature(string $feature, $default = null)
    {
        if (!$this->subscriptionPlan) {
            return $default;
        }

        return $this->subscriptionPlan->getFeature($feature, $default);
    }

    /**
     * Check if agency is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if agency is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function trialExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Get storage usage percentage
     */
    public function getStoragePercentageAttribute(): float
    {
        if ($this->storage_limit_mb == 0) {
            return 0;
        }

        return round(($this->storage_used_mb / $this->storage_limit_mb) * 100, 1);
    }

    /**
     * Get sites usage percentage
     */
    public function getSitesPercentageAttribute(): float
    {
        if ($this->site_limit == 0) {
            return 0;
        }

        return round(($this->sites_used / $this->site_limit) * 100, 1);
    }

    /**
     * Scope for active agencies
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for trial agencies
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }
}
