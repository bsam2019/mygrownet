<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaQuotationItemModel extends Model
{
    protected $table = 'sa_quotation_items';
    protected $fillable = ['sa_quotation_id', 'sa_item_id', 'item_name', 'quantity', 'unit_price', 'total'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo { return $this->belongsTo(SaQuotationModel::class, 'sa_quotation_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
