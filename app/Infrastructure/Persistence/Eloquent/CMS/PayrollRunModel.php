<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRunModel extends Model
{
    protected $table = 'cms_payroll_runs';

    protected $fillable = [
        'company_id', 'payroll_number', 'period_type', 'period_start', 'period_end',
        'total_wages', 'total_commissions', 'total_deductions', 'total_net_pay',
        'status', 'approved_by', 'approved_at', 'paid_date', 'notes', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_wages' => 'decimal:2',
        'total_commissions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net_pay' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItemModel::class, 'payroll_run_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'approved_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
