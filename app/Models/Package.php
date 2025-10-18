<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'duration_months',
        'features',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'duration_months' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * Get all subscriptions for this package
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active subscriptions for this package
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Get monthly equivalent price
     */
    public function getMonthlyPriceAttribute(): float
    {
        return $this->price / $this->duration_months;
    }

    /**
     * Check if package is monthly
     */
    public function isMonthly(): bool
    {
        return $this->billing_cycle === 'monthly';
    }

    /**
     * Check if package is annual
     */
    public function isAnnual(): bool
    {
        return $this->billing_cycle === 'annual';
    }
}
