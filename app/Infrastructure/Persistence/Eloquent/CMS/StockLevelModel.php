<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevelModel extends Model
{
    protected $table = 'cms_stock_levels';

    protected $fillable = [
        'location_id',
        'material_id',
        'quantity',
        'reserved_quantity',
        'unit',
        'reorder_level',
        'max_level',
        'last_counted_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'max_level' => 'decimal:2',
        'last_counted_date' => 'date',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'location_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function getAvailableQuantityAttribute(): float
    {
        return $this->quantity - $this->reserved_quantity;
    }
}
