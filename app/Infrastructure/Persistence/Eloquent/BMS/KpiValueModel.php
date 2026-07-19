<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiValueModel extends Model
{
    protected $table = 'cms_kpi_values';

    protected $fillable = [
        'kpi_id', 'period_date', 'period_start', 'period_end',
        'value', 'target_value', 'variance', 'variance_percentage',
        'notes', 'recorded_by',
    ];

    protected $casts = [
        'value' => 'float',
        'target_value' => 'float',
        'variance' => 'float',
        'variance_percentage' => 'float',
        'period_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KpiModel::class, 'kpi_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
