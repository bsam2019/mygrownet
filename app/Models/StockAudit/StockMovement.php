<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $table = 'sa_stock_movements';

    protected $fillable = [
        'sa_company_id', 'sa_item_id', 'sa_bin_id',
        'type', 'quantity', 'unit_price', 'total_value',
        'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_value' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
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
