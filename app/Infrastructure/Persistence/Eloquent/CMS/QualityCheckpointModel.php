<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheckpointModel extends Model
{
    protected $table = 'cms_quality_checkpoints';

    protected $fillable = [
        'production_order_id',
        'checkpoint_name',
        'stage',
        'status',
        'inspector_id',
        'inspected_at',
        'findings',
        'corrective_action',
        'requires_rework',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'requires_rework' => 'boolean',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'inspector_id');
    }
}
