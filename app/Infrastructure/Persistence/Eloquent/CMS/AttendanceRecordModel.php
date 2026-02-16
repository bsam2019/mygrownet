<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceRecordModel extends Model
{
    protected $table = 'cms_attendance_records';

    protected $fillable = [
        'company_id',
        'worker_id',
        'shift_id',
        'attendance_date',
        'clock_in_time',
        'clock_in_location',
        'clock_in_photo_path',
        'clock_in_device',
        'clock_out_time',
        'clock_out_location',
        'clock_out_photo_path',
        'clock_out_device',
        'total_minutes',
        'regular_minutes',
        'overtime_minutes',
        'break_minutes',
        'status',
        'is_late',
        'late_by_minutes',
        'is_early_departure',
        'early_by_minutes',
        'notes',
        'worker_notes',
        'is_manual_entry',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_in_location' => 'array',
        'clock_out_time' => 'datetime',
        'clock_out_location' => 'array',
        'total_minutes' => 'integer',
        'regular_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'break_minutes' => 'integer',
        'is_late' => 'boolean',
        'late_by_minutes' => 'integer',
        'is_early_departure' => 'boolean',
        'early_by_minutes' => 'integer',
        'is_manual_entry' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftModel::class, 'shift_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'approved_by');
    }

    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecordModel::class, 'attendance_record_id');
    }

    /**
     * Check if worker is currently clocked in
     */
    public function isClockedIn(): bool
    {
        return $this->clock_in_time !== null && $this->clock_out_time === null;
    }

    /**
     * Get total hours worked
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->total_minutes ? round($this->total_minutes / 60, 2) : 0;
    }

    /**
     * Get regular hours
     */
    public function getRegularHoursAttribute(): float
    {
        return $this->regular_minutes ? round($this->regular_minutes / 60, 2) : 0;
    }

    /**
     * Get overtime hours
     */
    public function getOvertimeHoursAttribute(): float
    {
        return $this->overtime_minutes ? round($this->overtime_minutes / 60, 2) : 0;
    }

    /**
     * Scope to get records for a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get present records
     */
    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late', 'half_day']);
    }

    /**
     * Scope to get absent records
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }
}
