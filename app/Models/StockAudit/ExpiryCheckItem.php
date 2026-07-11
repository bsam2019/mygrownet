<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryCheckItem extends Model
{
    protected $table = 'sa_expiry_check_items';

    protected $fillable = [
        'sa_expiry_check_id', 'sa_item_id', 'item_name',
        'unit_price', 'system_qty', 'physical_qty',
        'system_value', 'physical_value', 'missing_qty', 'missing_value', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'system_qty' => 'decimal:2',
            'physical_qty' => 'decimal:2',
            'system_value' => 'decimal:2',
            'physical_value' => 'decimal:2',
            'missing_qty' => 'decimal:2',
            'missing_value' => 'decimal:2',
        ];
    }

    public function expiryCheck(): BelongsTo
    {
        return $this->belongsTo(ExpiryCheck::class, 'sa_expiry_check_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'sa_item_id');
    }
}
