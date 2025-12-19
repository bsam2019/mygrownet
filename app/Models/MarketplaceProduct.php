<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceProduct extends Model
{
    protected $table = 'marketplace_products';

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'stock_quantity',
        'images',
        'status',
        'is_featured',
        'views',
        'rejection_reason',
    ];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'price' => 'integer',
        'compare_price' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'product_id');
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])->map(fn($img) => asset('storage/' . $img))->toArray();
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return isset($this->images[0]) ? asset('storage/' . $this->images[0]) : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'K' . number_format($this->price / 100, 2);
    }

    public function getFormattedComparePriceAttribute(): ?string
    {
        return $this->compare_price ? 'K' . number_format($this->compare_price / 100, 2) : null;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return 0;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    public function canBePurchased(int $quantity = 1): bool
    {
        return $this->isAvailable() && $this->stock_quantity >= $quantity;
    }
}
