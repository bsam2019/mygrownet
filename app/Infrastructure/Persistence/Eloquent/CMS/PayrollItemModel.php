<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItemModel extends Model
{
    protected $table = 'cms_payroll_items';

    protected $fillable = [
        'company_id', 'payroll_run_id', 'worker_id', 'cms_user_id',
        'wages', 'commissions', 'bonuses', 'deductions', 'net_pay',
        // Enhanced payroll fields
        'basic_salary', 'total_allowances', 'overtime_pay', 'bonus', 'commission', 'gross_pay',
        'napsa_employee', 'napsa_employer', 'nhima', 'paye',
        'total_statutory_deductions', 'total_other_deductions', 'total_deductions',
        'working_days', 'days_worked', 'days_absent', 'days_on_leave',
        'payment_status', 'payment_date', 'payment_reference',
    ];

    protected $casts = [
        'wages' => 'decimal:2',
        'commissions' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'commission' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'napsa_employee' => 'decimal:2',
        'napsa_employer' => 'decimal:2',
        'nhima' => 'decimal:2',
        'paye' => 'decimal:2',
        'total_statutory_deductions' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRunModel::class, 'payroll_run_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function cmsUser(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'cms_user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollItemDetailModel::class, 'payroll_item_id');
    }
}
