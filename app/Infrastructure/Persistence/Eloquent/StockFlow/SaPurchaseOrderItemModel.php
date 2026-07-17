<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaPurchaseOrderItemModel extends Model
{
    protected $table = 'sa_purchase_order_items';
    protected $fillable = ['sa_purchase_order_id', 'sa_item_id', 'sa_lot_id', 'quantity_ordered', 'quantity_received', 'unit_cost', 'total_cost'];
    protected $casts = [
        'quantity_ordered' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(SaPurchaseOrderModel::class, 'sa_purchase_order_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function lot(): BelongsTo { return $this->belongsTo(SaLotModel::class, 'sa_lot_id'); }
}
