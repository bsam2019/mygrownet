<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacityForecastModel extends Model
{
    protected $table = 'cms_capacity_forecasts';

    protected $fillable = [
        'company_id',
        'user_id',
        'forecast_date',
        'available_hours',
        'allocated_hours',
        'utilization_rate',
        'notes',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'available_hours' => 'decimal:2',
        'allocated_hours' => 'decimal:2',
        'utilization_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
