<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseModel extends Model
{
    protected $table = 'cms_expenses';

    protected $fillable = [
        'company_id',
        'branch_id',
        'expense_number',
        'category_id',
        'job_id',
        'description',
        'amount',
        'payment_method',
        'receipt_number',
        'receipt_path',
        'expense_date',
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requires_approval' => 'boolean',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategoryModel::class, 'category_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }
}
