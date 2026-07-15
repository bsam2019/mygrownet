<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaReceiptItemModel extends Model
{
    protected $table = 'sa_receipt_items';
    protected $fillable = ['sa_receipt_id', 'item_description', 'quantity', 'unit_price', 'total'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function receipt(): BelongsTo { return $this->belongsTo(SaReceiptModel::class, 'sa_receipt_id'); }
}
