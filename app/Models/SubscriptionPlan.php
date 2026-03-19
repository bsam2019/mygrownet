<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'annual_price',
        'site_limit',
        'storage_limit_mb',
        'team_member_limit',
        'client_limit',
        'features_json',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'features_json' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get agencies subscribed to this plan
     */
    public function agencies()
    {
        return $this->hasMany(Agency::class, 'subscription_plan_id');
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return ($this->features_json[$feature] ?? false) !== false;
    }

    /**
     * Get feature value
     */
    public function getFeature(string $feature, $default = null)
    {
        return $this->features_json[$feature] ?? $default;
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
