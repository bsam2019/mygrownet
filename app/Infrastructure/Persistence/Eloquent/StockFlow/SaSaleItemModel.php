<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaSaleItemModel extends Model
{
    protected $table = 'sa_sale_items';
    protected $fillable = ['sa_sale_id', 'sa_item_id', 'sa_lot_id', 'item_name', 'quantity', 'unit_price', 'total'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale(): BelongsTo { return $this->belongsTo(SaSaleModel::class, 'sa_sale_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function lot(): BelongsTo { return $this->belongsTo(SaLotModel::class, 'sa_lot_id'); }
}
