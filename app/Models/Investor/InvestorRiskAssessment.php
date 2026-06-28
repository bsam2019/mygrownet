<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorRiskAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_account_id',
        'assessment_period',
        'market_risk_score',
        'liquidity_risk_score',
        'operational_risk_score',
        'overall_risk_score',
        'risk_level',
        'risk_factors',
        'mitigation_strategies',
        'analyst_notes',
        'assessed_at',
    ];

    protected $casts = [
        'market_risk_score' => 'decimal:2',
        'liquidity_risk_score' => 'decimal:2',
        'operational_risk_score' => 'decimal:2',
        'overall_risk_score' => 'decimal:2',
        'risk_factors' => 'array',
        'mitigation_strategies' => 'array',
        'assessed_at' => 'datetime',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function getRiskLevelColorAttribute(): string
    {
        return match ($this->risk_level) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'red',
            default => 'gray',
        };
    }
}
