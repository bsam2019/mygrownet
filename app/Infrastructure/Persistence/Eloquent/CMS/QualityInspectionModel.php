<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityInspectionModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_quality_inspections';

    protected $fillable = [
        'company_id',
        'checklist_id',
        'job_id',
        'production_order_id',
        'inspection_date',
        'inspected_by',
        'inspection_stage',
        'overall_result',
        'notes',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(QualityChecklistModel::class, 'checklist_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderModel::class, 'production_order_id');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(QualityInspectionResultModel::class, 'inspection_id');
    }
}
