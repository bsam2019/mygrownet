<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaStockMovementModel extends Model
{
    protected $table = 'sa_stock_movements';
    protected $fillable = [
        'sa_company_id', 'sa_item_id', 'sa_bin_id', 'type',
        'quantity', 'unit_price', 'total_value',
        'quantity_before', 'quantity_after', 'reason',
        'reference_type', 'reference_id', 'created_by',
    ];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(SaBinModel::class, 'sa_bin_id'); }
}
