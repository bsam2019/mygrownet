<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReworkRecordModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_rework_records';

    protected $fillable = [
        'company_id',
        'rework_number',
        'rework_date',
        'job_id',
        'production_order_id',
        'ncr_id',
        'reason',
        'rework_description',
        'estimated_hours',
        'actual_hours',
        'estimated_cost',
        'actual_cost',
        'authorized_by',
        'completed_by',
        'completed_date',
        'status',
    ];

    protected $casts = [
        'rework_date' => 'date',
        'completed_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function ncr(): BelongsTo
    {
        return $this->belongsTo(NonConformanceModel::class, 'ncr_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
