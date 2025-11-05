<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;

class EmployeeKpiTracking extends Model
{
    protected $table = 'employee_kpi_tracking';

    protected $fillable = [
        'employee_id',
        'position_kpi_id',
        'period_start',
        'period_end',
        'actual_value',
        'target_value',
        'achievement_percentage',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'actual_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id');
    }

    public function positionKpi(): BelongsTo
    {
        return $this->belongsTo(PositionKpi::class, 'position_kpi_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate achievement percentage based on actual vs target
     */
    public function calculateAchievement(): void
    {
        if ($this->target_value && $this->target_value > 0) {
            $this->achievement_percentage = ($this->actual_value / $this->target_value) * 100;
        }
    }

    /**
     * Check if KPI target was met
     */
    public function isTargetMet(): bool
    {
        return $this->achievement_percentage >= 100;
    }

    /**
     * Get performance status
     */
    public function getPerformanceStatus(): string
    {
        if ($this->achievement_percentage >= 100) {
            return 'excellent';
        } elseif ($this->achievement_percentage >= 80) {
            return 'good';
        } elseif ($this->achievement_percentage >= 60) {
            return 'needs_improvement';
        } else {
            return 'poor';
        }
    }
}
