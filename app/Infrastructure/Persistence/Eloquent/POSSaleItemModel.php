<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSaleItemModel extends Model
{
    protected $table = 'pos_sale_items';

    protected $fillable = [
        'sale_id',
        'inventory_item_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit',
        'unit_price',
        'cost_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSaleModel::class, 'sale_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'inventory_item_id');
    }

    public function getProfitAttribute(): float
    {
        return ($this->unit_price - $this->cost_price) * $this->quantity;
    }
}
