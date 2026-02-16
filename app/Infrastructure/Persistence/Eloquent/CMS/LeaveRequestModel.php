<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestModel extends Model
{
    protected $table = 'cms_leave_requests';

    protected $fillable = [
        'company_id',
        'leave_request_number',
        'worker_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'contact_during_leave',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveTypeModel::class, 'leave_type_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'rejected_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }
}
