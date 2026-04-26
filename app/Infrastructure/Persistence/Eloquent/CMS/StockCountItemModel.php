<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockCountItemModel extends Model
{
    protected $table = 'cms_stock_count_items';

    protected $fillable = [
        'count_id',
        'material_id',
        'location_id',
        'system_quantity',
        'counted_quantity',
        'variance',
        'variance_percentage',
        'unit',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
    ];

    public function count(): BelongsTo
    {
        return $this->belongsTo(StockCountModel::class, 'count_id');
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
