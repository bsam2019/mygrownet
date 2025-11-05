<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Infrastructure\Persistence\Eloquent\PositionModel;

class PositionKpi extends Model
{
    protected $table = 'position_kpis';

    protected $fillable = [
        'position_id',
        'kpi_name',
        'kpi_description',
        'target_value',
        'measurement_unit',
        'measurement_frequency',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(PositionModel::class, 'position_id');
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(EmployeeKpiTracking::class, 'position_kpi_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
