<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerAttendanceModel extends Model
{
    protected $table = 'cms_worker_attendance';

    protected $fillable = [
        'company_id', 'worker_id', 'job_id', 'work_date', 'hours_worked',
        'days_worked', 'amount_earned', 'work_description', 'status',
        'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours_worked' => 'decimal:2',
        'days_worked' => 'decimal:2',
        'amount_earned' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
