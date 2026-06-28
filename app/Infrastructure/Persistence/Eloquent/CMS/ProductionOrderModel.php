<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrderModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_production_orders';

    protected $fillable = [
        'company_id',
        'job_id',
        'order_number',
        'order_date',
        'required_date',
        'start_date',
        'completion_date',
        'status',
        'priority',
        'assigned_to',
        'notes',
        'estimated_hours',
        'actual_hours',
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function cuttingLists(): HasMany
    {
        return $this->hasMany(CuttingListModel::class, 'production_order_id');
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(ProductionTrackingModel::class, 'production_order_id');
    }

    public function qualityCheckpoints(): HasMany
    {
        return $this->hasMany(QualityCheckpointModel::class, 'production_order_id');
    }

    public function materialsUsage(): HasMany
    {
        return $this->hasMany(ProductionMaterialsUsageModel::class, 'production_order_id');
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(ProductionScheduleModel::class, 'production_order_id');
    }

    public function wasteRecords(): HasMany
    {
        return $this->hasMany(WasteTrackingModel::class, 'production_order_id');
    }
}
