<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrowBuilderProduct extends Model
{
    use SoftDeletes;

    protected $table = 'growbuilder_products';

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'compare_price',
        'images',
        'stock_quantity',
        'track_stock',
        'sku',
        'category',
        'variants',
        'attributes',
        'weight',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'variants' => 'array',
        'attributes' => 'array',
        'price' => 'integer',
        'compare_price' => 'integer',
        'stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getPriceInKwachaAttribute(): float
    {
        return $this->price / 100;
    }

    public function getComparePriceInKwachaAttribute(): ?float
    {
        return $this->compare_price ? $this->compare_price / 100 : null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'K' . number_format($this->price_in_kwacha, 2);
    }

    public function getMainImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function isInStock(): bool
    {
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }
}
