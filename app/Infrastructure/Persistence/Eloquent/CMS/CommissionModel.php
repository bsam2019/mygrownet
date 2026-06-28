<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionModel extends Model
{
    protected $table = 'cms_commissions';

    protected $fillable = [
        'company_id', 'worker_id', 'cms_user_id', 'job_id', 'invoice_id',
        'commission_type', 'base_amount', 'commission_rate', 'commission_amount',
        'description', 'status', 'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function cmsUser(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'cms_user_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
