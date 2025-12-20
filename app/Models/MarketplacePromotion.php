<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplacePromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'name',
        'description',
        'type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'applicable_products',
        'applicable_categories',
        'starts_at',
        'ends_at',
        'is_active',
        'usage_limit',
        'usage_count',
    ];

    protected $casts = [
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['is_valid', 'type_label'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
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
            'buy_x_get_y' => 'Buy X Get Y',
            'free_shipping' => 'Free Shipping',
            default => ucfirst($this->type),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
