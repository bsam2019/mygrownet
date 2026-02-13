<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CustomerModel extends Model
{
    protected $table = 'cms_customers';

    protected $fillable = [
        'company_id',
        'customer_number',
        'name',
        'email',
        'phone',
        'secondary_phone',
        'address',
        'city',
        'country',
        'tax_number',
        'credit_limit',
        'outstanding_balance',
        'credit_balance',
        'credit_notes',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'credit_balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobModel::class, 'customer_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CustomerDocumentModel::class, 'customer_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContactModel::class, 'customer_id');
    }

    public function primaryContact()
    {
        return $this->hasOne(CustomerContactModel::class, 'customer_id')->where('is_primary', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->outstanding_balance > 0;
    }

    public function isOverCreditLimit(): bool
    {
        return $this->outstanding_balance > $this->credit_limit;
    }

    public function canTakeMoreCredit(float $amount): bool
    {
        return ($this->outstanding_balance + $amount) <= $this->credit_limit;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeWithOutstandingBalance(Builder $query): Builder
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
