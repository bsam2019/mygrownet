<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowBizPOSSaleItemModel extends Model
{
    protected $table = 'growbiz_pos_sale_items';

    protected $fillable = [
        'sale_id',
        'inventory_item_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit',
        'unit_price',
        'discount',
        'tax',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(GrowBizPOSSaleModel::class, 'sale_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'inventory_item_id');
    }
}
