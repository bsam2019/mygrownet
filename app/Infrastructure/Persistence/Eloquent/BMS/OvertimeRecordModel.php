<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRecordModel extends Model
{
    protected $table = 'cms_overtime_records';

    protected $fillable = [
        'company_id',
        'worker_id',
        'attendance_record_id',
        'overtime_date',
        'overtime_minutes',
        'overtime_type',
        'overtime_rate_multiplier',
        'base_hourly_rate',
        'overtime_amount',
        'reason',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'included_in_payroll',
        'payroll_id',
        'created_by',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'overtime_minutes' => 'integer',
        'overtime_rate_multiplier' => 'decimal:2',
        'base_hourly_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'included_in_payroll' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecordModel::class, 'attendance_record_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'approved_by');
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(PayrollRunModel::class, 'payroll_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    /**
     * Get overtime hours
     */
    public function getOvertimeHoursAttribute(): float
    {
        return round($this->overtime_minutes / 60, 2);
    }

    /**
     * Calculate overtime amount
     */
    public function calculateAmount(): float
    {
        if (!$this->base_hourly_rate) {
            return 0;
        }

        $hours = $this->overtime_minutes / 60;
        return round($hours * $this->base_hourly_rate * $this->overtime_rate_multiplier, 2);
    }

    /**
     * Scope to get pending overtime
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved overtime
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get overtime not included in payroll
     */
    public function scopeNotInPayroll($query)
    {
        return $query->where('included_in_payroll', false);
    }
}
