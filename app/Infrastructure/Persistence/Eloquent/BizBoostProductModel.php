<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BizBoostProductModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_products';

    protected $fillable = [
        'business_id',
        'name',
        'sku',
        'description',
        'price',
        'sale_price',
        'currency',
        'category',
        'category_id',
        'stock_quantity',
        'track_inventory',
        'is_active',
        'is_featured',
        'sort_order',
        'attributes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'attributes' => 'array',
    ];

    protected $appends = ['image_url'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(BizBoostCategoryModel::class, 'category_id');
    }

    /**
     * Get the category name (from model or legacy string field).
     */
    public function getCategoryNameAttribute(): ?string
    {
        if ($this->categoryModel) {
            return $this->categoryModel->name;
        }
        return $this->category;
    }

    public function images(): HasMany
    {
        return $this->hasMany(BizBoostProductImageModel::class, 'product_id')->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(BizBoostProductImageModel::class, 'product_id')->where('is_primary', true);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(BizBoostSaleModel::class, 'product_id');
    }

    public function getDisplayPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get the primary image URL for the product.
     */
    public function getImageUrlAttribute(): ?string
    {
        $primaryImage = $this->primaryImage;
        
        if ($primaryImage && $primaryImage->path) {
            return Storage::disk('public')->url($primaryImage->path);
        }

        // Fallback to first image if no primary is set
        $firstImage = $this->images()->first();
        if ($firstImage && $firstImage->path) {
            return Storage::disk('public')->url($firstImage->path);
        }

        return null;
    }
}
