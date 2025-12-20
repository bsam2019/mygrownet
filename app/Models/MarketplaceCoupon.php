<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'code',
        'description',
        'type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'starts_at',
        'ends_at',
        'is_active',
        'usage_limit',
        'usage_count',
        'per_user_limit',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['is_valid', 'type_label'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MarketplaceCouponUsage::class, 'coupon_id');
    }

    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($now->lt($this->starts_at) || $now->gt($this->ends_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'percentage' => 'Percentage Discount',
            'fixed_amount' => 'Fixed Amount Off',
            'free_shipping' => 'Free Shipping',
            default => ucfirst($this->type),
        };
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->is_valid) {
            return false;
        }

        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        return $userUsageCount < $this->per_user_limit;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
