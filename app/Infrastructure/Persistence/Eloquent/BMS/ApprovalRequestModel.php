<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalRequestModel extends Model
{
    protected $table = 'cms_approval_requests';

    protected $fillable = [
        'company_id',
        'approvable_type',
        'approvable_id',
        'request_type',
        'amount',
        'status',
        'requested_by',
        'request_notes',
        'approval_level',
        'required_levels',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approval_level' => 'integer',
        'required_levels' => 'integer',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'requested_by');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStepModel::class, 'approval_request_id');
    }

    public function currentStep(): ?ApprovalStepModel
    {
        return $this->steps()
            ->where('step_level', $this->approval_level)
            ->where('status', 'pending')
            ->first();
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

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('request_type', $type);
    }
}
