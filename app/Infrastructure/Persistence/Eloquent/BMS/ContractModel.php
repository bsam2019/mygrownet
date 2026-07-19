<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ContractModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_contracts';

    protected $fillable = [
        'company_id', 'branch_id', 'contract_number', 'template_id', 'customer_id',
        'title', 'description', 'start_date', 'end_date', 'renewal_date',
        'status', 'total_value', 'currency', 'terms', 'notes',
        'signed_by_customer', 'signed_by_company', 'signed_at',
        'signed_pdf_path', 'signing_token',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'total_value' => 'decimal:2',
        'signed_by_customer' => 'boolean',
        'signed_by_company' => 'boolean',
        'signed_at' => 'datetime',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplateModel::class, 'template_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(ContractRenewalModel::class, 'contract_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(ContractSignatureModel::class, 'contract_id');
    }

    public function scopeForBranch(Builder $query, ?int $branchId): Builder
    {
        return $branchId ? $query->where('branch_id', $branchId) : $query;
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereIn('status', ['active'])
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays($days));
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('end_date', '<', now());
    }

    public function markAsApproved(): void
    {
        $this->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);
    }

    public function markAsRejected(?string $reason = null): void
    {
        $this->update([
            'status' => 'draft',
            'rejection_reason' => $reason,
        ]);
    }
}
