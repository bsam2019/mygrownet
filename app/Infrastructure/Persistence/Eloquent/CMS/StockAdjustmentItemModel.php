<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItemModel extends Model
{
    protected $table = 'cms_stock_adjustment_items';

    protected $fillable = [
        'adjustment_id',
        'material_id',
        'location_id',
        'quantity',
        'unit',
        'unit_cost',
        'total_value',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustmentModel::class, 'adjustment_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'location_id');
    }
}
