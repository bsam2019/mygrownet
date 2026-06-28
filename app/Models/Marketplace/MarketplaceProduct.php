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
        'bizboost_product_id',
        'is_bizboost_synced',
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
        'rejection_category',
        'field_feedback',
        'appeal_message',
        'appealed_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'images' => 'array',
        'field_feedback' => 'array',
        'is_featured' => 'boolean',
        'is_bizboost_synced' => 'boolean',
        'price' => 'integer',
        'compare_price' => 'integer',
        'appealed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'image_urls',
        'primary_image_url',
        'formatted_price',
        'formatted_compare_price',
        'discount_percentage',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'seller_id');
    }

    public function bizboostProduct(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\BizBoostProductModel::class, 'bizboost_product_id');
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
        return collect($this->images ?? [])->map(function ($img) {
            // Handle if img is an array (shouldn't happen but defensive)
            if (is_array($img)) {
                return null;
            }
            
            // Check if it's already a full URL
            if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) {
                return $img;
            }
            
            // Check if it starts with 'marketplace/' (from media library)
            if (is_string($img) && str_starts_with($img, 'marketplace/')) {
                return \Storage::url($img);
            }
            
            return asset('storage/' . $img);
        })->filter()->values()->toArray();
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        if (!isset($this->images[0])) {
            return null;
        }
        
        $img = $this->images[0];
        
        // Handle if img is an array (shouldn't happen but defensive)
        if (is_array($img)) {
            return null;
        }
        
        // Check if it's already a full URL
        if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) {
            return $img;
        }
        
        // Check if it starts with 'marketplace/' (from media library)
        if (is_string($img) && str_starts_with($img, 'marketplace/')) {
            return \Storage::url($img);
        }
        
        return asset('storage/' . $img);
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
