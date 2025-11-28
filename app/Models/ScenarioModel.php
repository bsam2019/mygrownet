<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScenarioModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'scenario_type',
        'assumptions',
        'projections',
        'projected_valuation_1y',
        'projected_valuation_3y',
        'projected_valuation_5y',
        'projected_roi_1y',
        'projected_roi_3y',
        'projected_roi_5y',
        'is_active',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'projections' => 'array',
        'projected_valuation_1y' => 'decimal:2',
        'projected_valuation_3y' => 'decimal:2',
        'projected_valuation_5y' => 'decimal:2',
        'projected_roi_1y' => 'decimal:2',
        'projected_roi_3y' => 'decimal:2',
        'projected_roi_5y' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function getActiveScenarios(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->get();
    }
}
