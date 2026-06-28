<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonConformanceModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_non_conformances';

    protected $fillable = [
        'company_id',
        'ncr_number',
        'reported_date',
        'job_id',
        'production_order_id',
        'inspection_id',
        'non_conformance_type',
        'severity',
        'description',
        'root_cause',
        'reported_by',
        'assigned_to',
        'status',
        'closed_date',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'closed_date' => 'date',
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

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(QualityInspectionModel::class, 'inspection_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveActionModel::class, 'ncr_id');
    }
}
