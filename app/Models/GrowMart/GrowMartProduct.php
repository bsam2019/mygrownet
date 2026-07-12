<?php

namespace App\Models\GrowMart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GrowMartProduct extends Model
{
    use HasFactory;
    protected $table = 'growmart_products';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'unit',
        'price',
        'compare_price',
        'category_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_price' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GrowMartCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GrowMartProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(GrowMartInventory::class, 'product_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'K' . number_format($this->price / 100, 2);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->inventory()->sum('quantity');
    }
}
