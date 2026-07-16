<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaSaleReturnItemModel extends Model
{
    protected $table = 'sa_sale_return_items';
    public $timestamps = false;
    protected $fillable = ['sa_sale_return_id', 'sa_item_id', 'quantity', 'unit_price', 'subtotal', 'condition'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function return(): BelongsTo { return $this->belongsTo(SaSaleReturnModel::class, 'sa_sale_return_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
