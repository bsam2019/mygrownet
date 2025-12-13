<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItemModel extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'user_id',
        'name',
        'sku',
        'description',
        'category_id',
        'unit',
        'cost_price',
        'selling_price',
        'current_stock',
        'low_stock_threshold',
        'location',
        'barcode',
        'image_path',
        'is_active',
        'track_stock',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategoryModel::class, 'category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovementModel::class, 'item_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(InventoryAlertModel::class, 'item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStockValueAttribute(): float
    {
        return $this->current_stock * $this->cost_price;
    }

    public function getRetailValueAttribute(): float
    {
        return $this->current_stock * $this->selling_price;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) return 0;
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }
}
