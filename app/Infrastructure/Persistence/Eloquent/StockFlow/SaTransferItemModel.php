<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaTransferItemModel extends Model
{
    public $timestamps = false;
    protected $table = 'sa_transfer_items';
    protected $fillable = ['sa_transfer_id', 'sa_item_id', 'quantity', 'unit_cost', 'created_at'];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function transfer(): BelongsTo { return $this->belongsTo(SaTransferModel::class, 'sa_transfer_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
}
