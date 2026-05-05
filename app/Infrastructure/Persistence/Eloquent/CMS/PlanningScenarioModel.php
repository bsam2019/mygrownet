<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningScenarioModel extends Model
{
    protected $table = 'cms_planning_scenarios';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'scenario_type',
        'scenario_data',
        'results',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'scenario_data' => 'array',
        'results' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
