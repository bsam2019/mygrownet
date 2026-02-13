<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemModel extends Model
{
    protected $table = 'cms_payroll_items';

    protected $fillable = [
        'company_id', 'payroll_run_id', 'worker_id', 'cms_user_id',
        'wages', 'commissions', 'bonuses', 'deductions', 'net_pay',
    ];

    protected $casts = [
        'wages' => 'decimal:2',
        'commissions' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
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
}
