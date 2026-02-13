<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItemModel extends Model
{
    protected $table = 'cms_purchase_order_items';

    protected $fillable = [
        'purchase_order_id', 'inventory_item_id', 'description', 'quantity', 'unit',
        'unit_price', 'total_price', 'quantity_received', 'quantity_pending',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'quantity_pending' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItemModel::class, 'inventory_item_id');
    }
}
