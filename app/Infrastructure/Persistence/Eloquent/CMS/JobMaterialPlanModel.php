<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class JobMaterialPlanModel extends Model
{
    protected $table = 'cms_job_material_plans';

    protected $fillable = [
        'job_id',
        'material_id',
        'planned_quantity',
        'unit_price',
        'total_cost',
        'actual_quantity',
        'actual_unit_price',
        'actual_total_cost',
        'wastage_percentage',
        'notes',
        'status',
        'ordered_at',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'actual_unit_price' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
        'wastage_percentage' => 'decimal:2',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobModel::class, 'job_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->planned_quantity * $this->unit_price;
    }

    public function calculateActualTotalCost(): void
    {
        if ($this->actual_quantity && $this->actual_unit_price) {
            $this->actual_total_cost = $this->actual_quantity * $this->actual_unit_price;
        }
    }

    public function getVariance(): ?float
    {
        if ($this->actual_total_cost !== null) {
            return $this->actual_total_cost - $this->total_cost;
        }
        return null;
    }

    public function getVariancePercentage(): ?float
    {
        if ($this->actual_total_cost !== null && $this->total_cost > 0) {
            return (($this->actual_total_cost - $this->total_cost) / $this->total_cost) * 100;
        }
        return null;
    }

    public function markAsOrdered(): void
    {
        $this->update([
            'status' => 'ordered',
            'ordered_at' => now(),
        ]);
    }

    public function markAsReceived(float $actualQuantity, float $actualUnitPrice): void
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
            'actual_quantity' => $actualQuantity,
            'actual_unit_price' => $actualUnitPrice,
            'actual_total_cost' => $actualQuantity * $actualUnitPrice,
        ]);
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => 'used']);
    }

    public function scopeForJob(Builder $query, int $jobId): Builder
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', 'planned');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived(Builder $query): Builder
    {
        return $query->where('status', 'received');
    }
}
