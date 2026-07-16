<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaRequisitionItemModel extends Model
{
    protected $table = 'sa_requisition_items';
    protected $fillable = ['sa_purchase_requisition_id', 'sa_item_id', 'quantity', 'estimated_unit_price', 'notes'];
    protected $casts = ['quantity' => 'decimal:2', 'estimated_unit_price' => 'decimal:2'];

    public function requisition(): BelongsTo { return $this->belongsTo(SaPurchaseRequisitionModel::class, 'sa_purchase_requisition_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
