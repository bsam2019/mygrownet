<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorScenarioProjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_account_id',
        'scenario_type',
        'projection_years',
        'projected_valuation',
        'projected_equity_value',
        'projected_roi_percentage',
        'projected_annual_dividends',
        'assumptions',
        'milestones',
        'generated_at',
    ];

    protected $casts = [
        'projected_valuation' => 'decimal:2',
        'projected_equity_value' => 'decimal:2',
        'projected_roi_percentage' => 'decimal:2',
        'projected_annual_dividends' => 'decimal:2',
        'assumptions' => 'array',
        'milestones' => 'array',
        'generated_at' => 'datetime',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccount::class);
    }

    public function getScenarioLabelAttribute(): string
    {
        return match ($this->scenario_type) {
            'conservative' => 'Conservative',
            'moderate' => 'Moderate',
            'optimistic' => 'Optimistic',
            default => ucfirst($this->scenario_type),
        };
    }
}
