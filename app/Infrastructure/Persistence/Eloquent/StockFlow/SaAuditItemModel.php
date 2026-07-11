<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaAuditItemModel extends Model
{
    protected $table = 'sa_audit_items';
    protected $fillable = [
        'sa_audit_id', 'sa_item_id', 'sa_bin_id', 'item_name',
        'unit_price', 'system_qty', 'physical_qty',
        'system_value', 'physical_value', 'gap_qty', 'gap_value',
    ];
    protected $casts = [
        'unit_price' => 'decimal:2',
        'system_qty' => 'decimal:2',
        'physical_qty' => 'decimal:2',
        'system_value' => 'decimal:2',
        'physical_value' => 'decimal:2',
        'gap_qty' => 'float',
        'gap_value' => 'decimal:2',
    ];

    public function audit(): BelongsTo { return $this->belongsTo(SaAuditModel::class, 'sa_audit_id'); }
    public function bin(): BelongsTo { return $this->belongsTo(SaBinModel::class, 'sa_bin_id'); }
}
