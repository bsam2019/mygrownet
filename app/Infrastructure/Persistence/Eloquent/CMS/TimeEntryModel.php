<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntryModel extends Model
{
    protected $table = 'cms_time_entries';

    protected $fillable = [
        'company_id', 'worker_id', 'job_id', 'created_by',
        'start_time', 'end_time', 'duration_minutes', 'is_running',
        'is_billable', 'hourly_rate', 'total_amount',
        'description', 'notes',
        'status', 'approved_by', 'approved_at', 'rejection_reason',
        'included_in_payroll', 'payroll_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_running' => 'boolean',
        'is_billable' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
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

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(PayrollRunModel::class, 'payroll_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeRunning($query)
    {
        return $query->where('is_running', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
