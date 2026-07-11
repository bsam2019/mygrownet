<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountItem extends Model
{
    protected $table = 'sa_count_items';

    protected $fillable = [
        'sa_physical_count_id', 'sa_item_id', 'sa_bin_id',
        'system_quantity', 'physical_quantity', 'variance',
        'unit_price', 'variance_value', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:2',
            'physical_quantity' => 'decimal:2',
            'variance' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'variance_value' => 'decimal:2',
        ];
    }

    public function physicalCount(): BelongsTo
    {
        return $this->belongsTo(PhysicalCount::class, 'sa_physical_count_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'sa_item_id');
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class, 'sa_bin_id');
    }
}
