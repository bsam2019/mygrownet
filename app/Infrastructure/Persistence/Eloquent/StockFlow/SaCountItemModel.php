<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCountItemModel extends Model
{
    protected $table = 'sa_count_items';
    protected $fillable = [
        'sa_physical_count_id', 'sa_item_id', 'sa_bin_id',
        'system_quantity', 'physical_quantity', 'variance',
        'unit_price', 'variance_value',
    ];
    protected $casts = [
        'system_quantity' => 'decimal:2',
        'physical_quantity' => 'decimal:2',
        'variance' => 'float',
        'unit_price' => 'decimal:2',
        'variance_value' => 'decimal:2',
    ];

    public function physicalCount(): BelongsTo { return $this->belongsTo(SaPhysicalCountModel::class, 'sa_physical_count_id'); }
    public function item(): BelongsTo { return $this->belongsTo(SaItemModel::class, 'sa_item_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(SaBinModel::class, 'sa_bin_id'); }
}
