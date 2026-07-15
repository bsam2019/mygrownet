<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaInvoiceItemModel extends Model
{
    protected $table = 'sa_invoice_items';
    protected $fillable = ['sa_invoice_id', 'sa_item_id', 'item_name', 'quantity', 'unit_price', 'total'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(SaInvoiceModel::class, 'sa_invoice_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
