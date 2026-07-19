<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningScenarioModel extends Model
{
    protected $table = 'cms_planning_scenarios';

    protected $fillable = [
        'company_id',
        'scenario_name',
        'description',
        'scenario_type',
        'changes_json',
        'impact_analysis_json',
        'original_state',
        'metrics_before',
        'metrics_after',
        'created_by',
        'applied_by',
        'applied_at',
    ];

    protected $casts = [
        'changes_json' => 'array',
        'impact_analysis_json' => 'array',
        'original_state' => 'array',
        'metrics_before' => 'array',
        'metrics_after' => 'array',
        'applied_at' => 'datetime',
    ];

    protected $appends = ['name', 'status', 'created_by_name'];

    // Accessor for 'name' to map to 'scenario_name'
    public function getNameAttribute()
    {
        return $this->scenario_name;
    }

    // Accessor for status based on applied_at
    public function getStatusAttribute()
    {
        if ($this->applied_at) {
            return 'applied';
        }
        return 'pending';
    }

    // Accessor for created_by_name
    public function getCreatedByNameAttribute()
    {
        return $this->creator?->name ?? 'Unknown';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
