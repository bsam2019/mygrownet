<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestmentTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'minimum_investment',
        'monthly_fee',
        'monthly_share',
        'required_team_volume',
        'required_active_referrals',
        'consecutive_months_required',
        'achievement_bonus',
        'monthly_team_volume_bonus_rate',
        'fixed_profit_rate',
        'direct_referral_rate',
        'level2_referral_rate',
        'level3_referral_rate',
        'reinvestment_bonus_rate',
        'quarterly_profit_sharing_eligible',
        'annual_profit_sharing_eligible',
        'profit_sharing_percentage',
        'leadership_bonus_eligible',
        'business_facilitation_eligible',
        'benefits',
        'is_active',
        'is_archived',
        'description',
        'order'
    ];

    protected $casts = [
        'minimum_investment' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'monthly_share' => 'decimal:2',
        'required_team_volume' => 'decimal:2',
        'achievement_bonus' => 'decimal:2',
        'monthly_team_volume_bonus_rate' => 'decimal:2',
        'fixed_profit_rate' => 'decimal:2',
        'direct_referral_rate' => 'decimal:2',
        'level2_referral_rate' => 'decimal:2',
        'level3_referral_rate' => 'decimal:2',
        'reinvestment_bonus_rate' => 'decimal:2',
        'profit_sharing_percentage' => 'decimal:2',
        'quarterly_profit_sharing_eligible' => 'boolean',
        'annual_profit_sharing_eligible' => 'boolean',
        'leadership_bonus_eligible' => 'boolean',
        'business_facilitation_eligible' => 'boolean',
        'benefits' => 'array',
        'is_active' => 'boolean',
        'is_archived' => 'boolean'
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'current_investment_tier', 'name');
    }

    public function settings()
    {
        return $this->hasOne(TierSetting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getNextTier()
    {
        return static::where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    public function getPreviousTier()
    {
        return static::where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }

    public function calculateMonthlyProfit($investmentAmount)
    {
        return ($investmentAmount * $this->fixed_profit_rate) / 100 / 12;
    }

    public function calculateReferralCommission($amount, $level = 1)
    {
        $rate = match($level) {
            1 => $this->direct_referral_rate,
            2 => $this->level2_referral_rate,
            3 => $this->level3_referral_rate,
            default => 0
        };

        return ($amount * $rate) / 100;
    }

    public function canUpgrade(User $user)
    {
        return $user->total_investment_amount >= $this->minimum_investment;
    }

    public function getUpgradeRequirements(User $user)
    {
        $nextTier = $this->getNextTier();
        if (!$nextTier) {
            return null;
        }

        $remaining = $nextTier->minimum_investment - $user->total_investment_amount;
        return [
            'next_tier' => $nextTier,
            'remaining_amount' => $remaining > 0 ? $remaining : 0,
            'can_upgrade' => $remaining <= 0
        ];
    }

    // VBIF-specific multi-level referral commission methods
    public function calculateMultiLevelReferralCommission(float $investmentAmount, int $level): float
    {
        $rate = $this->getReferralRateForLevel($level);
        return ($investmentAmount * $rate) / 100;
    }

    public function getReferralRateForLevel(int $level): float
    {
        return match($level) {
            1 => $this->direct_referral_rate ?? 0,
            2 => $this->level2_referral_rate ?? 0,
            3 => $this->level3_referral_rate ?? 0,
            default => 0
        };
    }

    public function getAllReferralRates(): array
    {
        return [
            'level_1' => $this->direct_referral_rate ?? 0,
            'level_2' => $this->level2_referral_rate ?? 0,
            'level_3' => $this->level3_referral_rate ?? 0,
            'max_levels' => $this->getMaxReferralLevels()
        ];
    }

    public function getMaxReferralLevels(): int
    {
        if ($this->level3_referral_rate > 0) return 3;
        if ($this->level2_referral_rate > 0) return 2;
        if ($this->direct_referral_rate > 0) return 1;
        return 0;
    }

    public function isEligibleForReferralLevel(int $level): bool
    {
        return match($level) {
            1 => true, // All tiers eligible for level 1
            2 => in_array($this->name, ['Starter', 'Builder', 'Leader', 'Elite']),
            3 => in_array($this->name, ['Builder', 'Leader', 'Elite']),
            default => false
        };
    }

    // Reinvestment bonus calculation methods
    public function calculateReinvestmentBonus(float $reinvestmentAmount): float
    {
        if (!$this->reinvestment_bonus_rate) {
            return 0;
        }

        return ($reinvestmentAmount * $this->reinvestment_bonus_rate) / 100;
    }

    public function getReinvestmentBonusRate(): float
    {
        return $this->reinvestment_bonus_rate ?? 0;
    }

    public function getEnhancedProfitRateForReinvestment(): float
    {
        // Enhanced rates for year 2+ reinvestments based on tier
        return match($this->name) {
            'Starter' => 8.0,
            'Builder' => 10.0,
            'Leader' => 12.0,
            'Elite' => 17.0,
            default => $this->fixed_profit_rate
        };
    }

    public function isEligibleForReinvestmentBonus(): bool
    {
        return $this->reinvestment_bonus_rate > 0;
    }

    // Tier comparison and upgrade requirement methods
    public function compareWith(InvestmentTier $otherTier): array
    {
        return [
            'current_tier' => [
                'name' => $this->name,
                'minimum_investment' => $this->minimum_investment,
                'profit_rate' => $this->fixed_profit_rate,
                'referral_rates' => $this->getAllReferralRates(),
                'reinvestment_bonus' => $this->reinvestment_bonus_rate
            ],
            'comparison_tier' => [
                'name' => $otherTier->name,
                'minimum_investment' => $otherTier->minimum_investment,
                'profit_rate' => $otherTier->fixed_profit_rate,
                'referral_rates' => $otherTier->getAllReferralRates(),
                'reinvestment_bonus' => $otherTier->reinvestment_bonus_rate
            ],
            'differences' => [
                'investment_increase' => $otherTier->minimum_investment - $this->minimum_investment,
                'profit_rate_increase' => $otherTier->fixed_profit_rate - $this->fixed_profit_rate,
                'referral_rate_improvements' => [
                    'level_1' => $otherTier->direct_referral_rate - $this->direct_referral_rate,
                    'level_2' => ($otherTier->level2_referral_rate ?? 0) - ($this->level2_referral_rate ?? 0),
                    'level_3' => ($otherTier->level3_referral_rate ?? 0) - ($this->level3_referral_rate ?? 0)
                ],
                'reinvestment_bonus_increase' => ($otherTier->reinvestment_bonus_rate ?? 0) - ($this->reinvestment_bonus_rate ?? 0)
            ]
        ];
    }

    public function getUpgradeBenefits(): array
    {
        $nextTier = $this->getNextTier();
        if (!$nextTier) {
            return [];
        }

        $comparison = $this->compareWith($nextTier);
        
        return [
            'next_tier' => $nextTier->name,
            'additional_investment_required' => $comparison['differences']['investment_increase'],
            'profit_rate_improvement' => $comparison['differences']['profit_rate_increase'],
            'referral_improvements' => $comparison['differences']['referral_rate_improvements'],
            'new_features' => $this->getNewFeaturesForTier($nextTier),
            'annual_profit_increase_estimate' => $this->estimateAnnualProfitIncrease($nextTier)
        ];
    }

    protected function getNewFeaturesForTier(InvestmentTier $tier): array
    {
        $features = [];
        
        if ($tier->level2_referral_rate > 0 && $this->level2_referral_rate <= 0) {
            $features[] = 'Level 2 referral commissions unlocked';
        }
        
        if ($tier->level3_referral_rate > 0 && $this->level3_referral_rate <= 0) {
            $features[] = 'Level 3 referral commissions unlocked';
        }
        
        if ($tier->reinvestment_bonus_rate > $this->reinvestment_bonus_rate) {
            $features[] = 'Enhanced reinvestment bonus rates';
        }

        return $features;
    }

    protected function estimateAnnualProfitIncrease(InvestmentTier $nextTier, float $assumedInvestment = null): float
    {
        $investment = $assumedInvestment ?? $nextTier->minimum_investment;
        
        $currentAnnualProfit = ($investment * $this->fixed_profit_rate) / 100;
        $nextTierAnnualProfit = ($investment * $nextTier->fixed_profit_rate) / 100;
        
        return $nextTierAnnualProfit - $currentAnnualProfit;
    }

    // Matrix commission calculation methods
    public function calculateMatrixCommission(float $investmentAmount, int $matrixLevel, int $position): float
    {
        // Matrix commissions are based on referral rates but with position multipliers
        $baseRate = $this->getReferralRateForLevel($matrixLevel);
        $positionMultiplier = $this->getMatrixPositionMultiplier($matrixLevel, $position);
        
        return ($investmentAmount * $baseRate * $positionMultiplier) / 100;
    }

    protected function getMatrixPositionMultiplier(int $level, int $position): float
    {
        // 3x3 matrix position multipliers
        return match($level) {
            1 => 1.0, // Direct referrals get full commission
            2 => 0.8, // Level 2 gets 80% of base rate
            3 => 0.6, // Level 3 gets 60% of base rate
            default => 0
        };
    }

    public function getMatrixCommissionStructure(): array
    {
        return [
            'level_1' => [
                'base_rate' => $this->direct_referral_rate,
                'positions' => 3,
                'multiplier' => 1.0,
                'effective_rate' => $this->direct_referral_rate
            ],
            'level_2' => [
                'base_rate' => $this->level2_referral_rate ?? 0,
                'positions' => 9,
                'multiplier' => 0.8,
                'effective_rate' => ($this->level2_referral_rate ?? 0) * 0.8
            ],
            'level_3' => [
                'base_rate' => $this->level3_referral_rate ?? 0,
                'positions' => 27,
                'multiplier' => 0.6,
                'effective_rate' => ($this->level3_referral_rate ?? 0) * 0.6
            ]
        ];
    }

    public function calculateMaxMatrixEarnings(float $investmentAmount): array
    {
        $structure = $this->getMatrixCommissionStructure();
        $maxEarnings = [];
        $totalMaxEarnings = 0;

        foreach ($structure as $level => $config) {
            $levelEarnings = ($investmentAmount * $config['effective_rate'] / 100) * $config['positions'];
            $maxEarnings[$level] = [
                'per_position' => ($investmentAmount * $config['effective_rate']) / 100,
                'max_positions' => $config['positions'],
                'max_level_earnings' => $levelEarnings
            ];
            $totalMaxEarnings += $levelEarnings;
        }

        $maxEarnings['total_max_earnings'] = $totalMaxEarnings;
        return $maxEarnings;
    }

    // MyGrowNet-specific methods
    public function calculateMonthlyShare(): float
    {
        return $this->monthly_share;
    }

    public function calculateTeamVolumeBonus(float $teamVolume): float
    {
        if ($this->monthly_team_volume_bonus_rate <= 0) {
            return 0;
        }
        
        return $teamVolume * ($this->monthly_team_volume_bonus_rate / 100);
    }

    public function qualifiesForUpgrade(int $activeReferrals, float $teamVolume): bool
    {
        return $activeReferrals >= $this->required_active_referrals && 
               $teamVolume >= $this->required_team_volume;
    }

    public function getMyGrowNetUpgradeRequirements(): array
    {
        return [
            'required_active_referrals' => $this->required_active_referrals,
            'required_team_volume' => $this->required_team_volume,
            'consecutive_months_required' => $this->consecutive_months_required,
            'achievement_bonus' => $this->achievement_bonus
        ];
    }

    public function isEligibleForProfitSharing(string $type = 'quarterly'): bool
    {
        return match($type) {
            'quarterly' => $this->quarterly_profit_sharing_eligible,
            'annual' => $this->annual_profit_sharing_eligible,
            default => false
        };
    }

    public function getProfitSharingPercentage(): float
    {
        return $this->profit_sharing_percentage;
    }

    public function isEligibleForBusinessFacilitation(): bool
    {
        return $this->business_facilitation_eligible;
    }

    public function isEligibleForLeadershipBonus(): bool
    {
        return $this->leadership_bonus_eligible;
    }

    public function getMyGrowNetBenefits(): array
    {
        return [
            'monthly_fee' => $this->monthly_fee,
            'monthly_share' => $this->monthly_share,
            'achievement_bonus' => $this->achievement_bonus,
            'team_volume_bonus_rate' => $this->monthly_team_volume_bonus_rate,
            'profit_sharing' => [
                'quarterly_eligible' => $this->quarterly_profit_sharing_eligible,
                'annual_eligible' => $this->annual_profit_sharing_eligible,
                'percentage' => $this->profit_sharing_percentage
            ],
            'leadership_bonus_eligible' => $this->leadership_bonus_eligible,
            'business_facilitation_eligible' => $this->business_facilitation_eligible,
            'benefits' => $this->benefits ?? []
        ];
    }

    public function getMyGrowNetCommissionStructure(): array
    {
        return [
            'level_1' => $this->direct_referral_rate, // 12%
            'level_2' => $this->level2_referral_rate, // 6%
            'level_3' => $this->level3_referral_rate, // 4%
            'level_4' => 2.0, // MyGrowNet Level 4: 2%
            'level_5' => 1.0, // MyGrowNet Level 5: 1%
            'team_volume_bonus_rate' => $this->monthly_team_volume_bonus_rate
        ];
    }

    // Tier-specific business rules
    public function getWithdrawalPenaltyReduction(): float
    {
        // Higher tiers get reduced withdrawal penalties
        return match($this->name) {
            'Elite' => 0.2, // 20% penalty reduction
            'Diamond' => 0.15, // 15% penalty reduction
            'Gold' => 0.1, // 10% penalty reduction
            'Silver' => 0.05, // 5% penalty reduction
            default => 0
        };
    }

    public function getMatrixSpilloverPriority(): int
    {
        // Higher tiers get priority in spillover placement
        return match($this->name) {
            'Elite' => 5,
            'Leader' => 4,
            'Builder' => 3,
            'Starter' => 2,
            'Basic' => 1,
            default => 0
        };
    }

    public function getTierSpecificBenefits(): array
    {
        return [
            'profit_rate' => $this->fixed_profit_rate,
            'referral_levels' => $this->getMaxReferralLevels(),
            'referral_rates' => $this->getAllReferralRates(),
            'reinvestment_bonus' => $this->reinvestment_bonus_rate ?? 0,
            'withdrawal_penalty_reduction' => $this->getWithdrawalPenaltyReduction(),
            'matrix_spillover_priority' => $this->getMatrixSpilloverPriority(),
            'enhanced_profit_rate_year2' => $this->getEnhancedProfitRateForReinvestment(),
            'matrix_commission_structure' => $this->getMatrixCommissionStructure()
        ];
    }
}