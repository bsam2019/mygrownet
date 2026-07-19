<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanObjectiveModel extends Model
{
    protected $table = 'plan_objectives';

    protected $fillable = [
        'plan_id',
        'kpi_id',
        'title',
        'description',
        'type',
        'target_value',
        'current_value',
        'unit',
        'owner',
        'target_date',
        'completed_at',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'target_value' => 'float',
        'current_value' => 'float',
        'target_date' => 'date',
        'completed_at' => 'date',
        'sort_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanModel::class, 'plan_id');
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KpiModel::class, 'kpi_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(PlanLinkModel::class, 'plan_objective_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $objective) {
            if ($objective->kpi_id && $objective->isDirty('kpi_id')) {
                $kpi = KpiModel::find($objective->kpi_id);
                $latest = $kpi?->latestValue();
                if ($latest) {
                    $objective->current_value = $latest->value;
                }
            }
        });
    }

    public function syncFromKpi(): bool
    {
        if (!$this->kpi_id) return false;
        $kpi = $this->kpi()->first();
        $latest = $kpi?->latestValue();
        if (!$latest) return false;
        $this->current_value = $latest->value;
        return $this->save();
    }

    public function progress(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return $this->status === 'completed' ? 100 : 0;
        }
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }
}
