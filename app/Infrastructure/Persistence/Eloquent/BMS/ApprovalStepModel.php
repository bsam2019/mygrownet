<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStepModel extends Model
{
    protected $table = 'cms_approval_steps';

    protected $fillable = [
        'approval_request_id',
        'step_level',
        'approver_id',
        'approver_role',
        'status',
        'comments',
        'actioned_at',
    ];

    protected $casts = [
        'step_level' => 'integer',
        'actioned_at' => 'datetime',
    ];

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequestModel::class, 'approval_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
