<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTrackingModel extends Model
{
    protected $table = 'cms_production_tracking';

    protected $fillable = [
        'production_order_id',
        'user_id',
        'stage',
        'status',
        'started_at',
        'completed_at',
        'hours_spent',
        'quantity_completed',
        'quantity_rejected',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'hours_spent' => 'decimal:2',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
