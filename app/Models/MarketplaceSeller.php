<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceSeller extends Model
{
    protected $table = 'marketplace_sellers';

    protected $fillable = [
        'user_id',
        'bizboost_business_id',
        'is_bizboost_synced',
        'business_name',
        'business_type',
        'province',
        'district',
        'phone',
        'email',
        'description',
        'logo_path',
        'trust_level',
        'commission_rate',
        'kyc_status',
        'kyc_documents',
        'kyc_rejection_reason',
        'total_orders',
        'completed_orders',
        'total_sales_amount',
        'dispute_rate',
        'cancellation_rate',
        'response_rate',
        'tier_calculated_at',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'kyc_documents' => 'array',
        'is_active' => 'boolean',
        'is_bizboost_synced' => 'boolean',
        'rating' => 'float',
        'commission_rate' => 'float',
        'dispute_rate' => 'float',
        'cancellation_rate' => 'float',
        'response_rate' => 'float',
        'tier_calculated_at' => 'datetime',
    ];

    protected $appends = [
        'logo_url',
        'trust_badge',
        'trust_label',
        'effective_commission_rate',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bizboostBusiness(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel::class, 'bizboost_business_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(MarketplaceProduct::class, 'seller_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MarketplaceOrder::class, 'seller_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(MarketplaceReview::class, 'seller_id');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(MarketplaceDispute::class, 'seller_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getTrustBadgeAttribute(): string
    {
        return match ($this->trust_level) {
            'new' => '🆕',
            'verified' => '✓',
            'trusted' => '⭐',
            'top' => '👑',
            default => '',
        };
    }

    public function getTrustLabelAttribute(): string
    {
        return match ($this->trust_level) {
            'new' => 'New Seller',
            'verified' => 'Verified Seller',
            'trusted' => 'Trusted Seller',
            'top' => 'Top Seller',
            default => 'Seller',
        };
    }

    public function canAcceptOrders(): bool
    {
        return $this->is_active && $this->kyc_status === 'approved';
    }

    /**
     * Get the effective commission rate (custom or tier-based)
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        if ($this->commission_rate && $this->commission_rate > 0) {
            return $this->commission_rate;
        }

        $rates = config('marketplace.commission.rates', []);
        return $rates[$this->trust_level] ?? 10.0;
    }

    /**
     * Get formatted total sales
     */
    public function getFormattedTotalSalesAttribute(): string
    {
        return 'K' . number_format(($this->total_sales_amount ?? 0) / 100, 2);
    }
}
