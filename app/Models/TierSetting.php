<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TierSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'investment_tier_id',
        'early_withdrawal_penalty',
        'partial_withdrawal_limit',
        'minimum_lock_in_period',
        'performance_bonus_rate',
        'additional_rules',
        'requires_kyc',
        'requires_approval'
    ];

    protected $casts = [
        'early_withdrawal_penalty' => 'decimal:2',
        'partial_withdrawal_limit' => 'decimal:2',
        'performance_bonus_rate' => 'decimal:2',
        'additional_rules' => 'array',
        'requires_kyc' => 'boolean',
        'requires_approval' => 'boolean'
    ];

    // Relationships
    public function tier()
    {
        return $this->belongsTo(InvestmentTier::class, 'investment_tier_id');
    }

    // Methods
    public function calculateEarlyWithdrawalPenalty($amount)
    {
        return ($amount * $this->early_withdrawal_penalty) / 100;
    }

    public function calculatePartialWithdrawalLimit($totalProfits)
    {
        return ($totalProfits * $this->partial_withdrawal_limit) / 100;
    }

    public function isWithinLockInPeriod($investmentDate)
    {
        $monthsSinceInvestment = $investmentDate->diffInMonths(now());
        return $monthsSinceInvestment >= $this->minimum_lock_in_period;
    }

    public function calculatePerformanceBonus($baseAmount)
    {
        if (!$this->performance_bonus_rate) {
            return 0;
        }
        return ($baseAmount * $this->performance_bonus_rate) / 100;
    }

    public function getAdditionalRule($key, $default = null)
    {
        return $this->additional_rules[$key] ?? $default;
    }
} 