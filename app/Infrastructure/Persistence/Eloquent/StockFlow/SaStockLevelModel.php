<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaStockLevelModel extends Model
{
    protected $table = 'sa_stock_levels';

    protected $fillable = [
        'sa_company_id', 'sa_item_id',
        'qty_on_hand', 'value_on_hand', 'last_movement_at',
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:2',
        'value_on_hand' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaCompanyModel::class, 'sa_company_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SaItemModel::class, 'sa_item_id');
    }
}
