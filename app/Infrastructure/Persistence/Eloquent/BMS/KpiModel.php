<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class KpiModel extends Model
{
    protected $table = 'cms_kpis';

    protected $fillable = [
        'company_id', 'name', 'description', 'category', 'metric_type',
        'calculation_method', 'data_source', 'unit', 'frequency',
        'target_min', 'target_max', 'direction', 'owner',
        'is_active', 'status', 'sort_order', 'created_by',
    ];

    protected $casts = [
        'target_min' => 'float',
        'target_max' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(KpiValueModel::class, 'kpi_id');
    }

    public function latestValue(): ?KpiValueModel
    {
        return $this->values()->orderBy('period_end', 'desc')->orderBy('period_date', 'desc')->first();
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getCategoryAttribute($value): string
    {
        return $value ?? $this->metric_type ?? 'financial';
    }

    public function getStatusAttribute($value): string
    {
        return $value ?? ($this->is_active ? 'active' : 'inactive');
    }

    public function trend(): string
    {
        $recent = $this->values()->orderBy('period_end', 'desc')->orderBy('period_date', 'desc')->take(3)->get();
        if ($recent->count() < 2) return 'insufficient_data';

        $vals = $recent->pluck('value');
        $goingUp = $vals->first() > $vals->last();
        $dir = $this->direction ?? 'up';
        if ($dir === 'up') return $goingUp ? 'improving' : 'declining';
        if ($dir === 'down') return $goingUp ? 'declining' : 'improving';
        return 'neutral';
    }

    public function statusColor(): string
    {
        $latest = $this->latestValue();
        if (!$latest) return 'bg-gray-100 text-gray-500';

        if ($this->target_min !== null && $latest->value < $this->target_min) return 'bg-red-100 text-red-800';
        if ($this->target_max !== null && $latest->value > $this->target_max) return 'bg-red-100 text-red-800';

        return 'bg-green-100 text-green-800';
    }
}
