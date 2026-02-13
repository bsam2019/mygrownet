<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerModel extends Model
{
    protected $table = 'cms_workers';

    protected $fillable = [
        'company_id', 'worker_number', 'name', 'phone', 'email', 'id_number',
        'worker_type', 'hourly_rate', 'daily_rate', 'commission_rate',
        'payment_method', 'mobile_money_number', 'bank_name', 'bank_account_number',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(WorkerAttendanceModel::class, 'worker_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(CommissionModel::class, 'worker_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
