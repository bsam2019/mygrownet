<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftModel extends Model
{
    protected $table = 'cms_shifts';

    protected $fillable = [
        'company_id',
        'shift_name',
        'shift_code',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'grace_period_minutes',
        'minimum_hours_full_day',
        'minimum_hours_half_day',
        'overtime_threshold_minutes',
        'is_night_shift',
        'night_shift_differential_percent',
        'is_weekend_shift',
        'weekend_differential_percent',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'break_duration_minutes' => 'integer',
        'grace_period_minutes' => 'integer',
        'minimum_hours_full_day' => 'decimal:2',
        'minimum_hours_half_day' => 'decimal:2',
        'overtime_threshold_minutes' => 'integer',
        'is_night_shift' => 'boolean',
        'night_shift_differential_percent' => 'decimal:2',
        'is_weekend_shift' => 'boolean',
        'weekend_differential_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workerShifts(): HasMany
    {
        return $this->hasMany(WorkerShiftModel::class, 'shift_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecordModel::class, 'shift_id');
    }

    public function workers(): HasMany
    {
        return $this->hasMany(WorkerModel::class, 'default_shift_id');
    }

    /**
     * Calculate shift duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        
        // Handle overnight shifts
        if ($end->lessThan($start)) {
            $end->addDay();
        }
        
        return $start->diffInMinutes($end) - $this->break_duration_minutes;
    }

    /**
     * Check if time is within grace period
     */
    public function isWithinGracePeriod(\Carbon\Carbon $clockInTime): bool
    {
        $shiftStart = \Carbon\Carbon::parse($this->start_time);
        $gracePeriodEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);
        
        return $clockInTime->lessThanOrEqualTo($gracePeriodEnd);
    }

    /**
     * Calculate late minutes
     */
    public function calculateLateMinutes(\Carbon\Carbon $clockInTime): int
    {
        $shiftStart = \Carbon\Carbon::parse($this->start_time);
        $gracePeriodEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);
        
        if ($clockInTime->lessThanOrEqualTo($gracePeriodEnd)) {
            return 0;
        }
        
        return $gracePeriodEnd->diffInMinutes($clockInTime);
    }
}
