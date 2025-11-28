<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_date',
        'overall_risk_score',
        'risk_factors',
        'mitigation_strategies',
        'summary',
        'risk_level',
        'assessed_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'risk_factors' => 'array',
        'mitigation_strategies' => 'array',
    ];

    public static function getLatest(): ?self
    {
        return static::orderBy('assessment_date', 'desc')->first();
    }

    public function getRiskColor(): string
    {
        return match($this->risk_level) {
            'low' => 'green',
            'moderate' => 'blue',
            'elevated' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }
}
