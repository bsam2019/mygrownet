<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaSupplierReturnItemModel extends Model
{
    protected $table = 'sa_supplier_return_items';
    public $timestamps = false;
    protected $fillable = ['sa_supplier_return_id', 'sa_item_id', 'quantity', 'unit_cost', 'subtotal'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function return(): BelongsTo { return $this->belongsTo(SaSupplierReturnModel::class, 'sa_supplier_return_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
