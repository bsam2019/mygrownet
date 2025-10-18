<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasActivityLogs;
use Carbon\Carbon;

class Investment extends Model
{
    use HasFactory, HasActivityLogs;

    protected $fillable = [
        'user_id',
        'tier_id',
        'category_id',
        'amount',
        'platform_fee',
        'tier',
        'expected_return',
        'roi',
        'total_earned',
        'status',
        'investment_date',
        'interest_earned',
        'lock_in_period_end',
        'last_payout_date',
        'maturity_date',
        'next_payment_date',
        'end_date',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
        // Community project integration fields
        'community_project_id',
        'is_community_investment',
        'investment_source',
        'community_contribution_amount',
        'tier_at_community_investment',
        'voting_power_weight',
        'participated_in_voting',
        'eligible_for_community_profits',
        'community_profit_share_percentage',
        'community_investment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'end_date' => 'datetime',
        'expected_return' => 'decimal:2',
        'next_payment_date' => 'datetime',
        'investment_date' => 'datetime',
        'lock_in_period_end' => 'datetime',
        'last_payout_date' => 'datetime',
        'maturity_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'rejected_at' => 'datetime',
        // Community project casts
        'is_community_investment' => 'boolean',
        'community_contribution_amount' => 'decimal:2',
        'voting_power_weight' => 'decimal:4',
        'participated_in_voting' => 'boolean',
        'eligible_for_community_profits' => 'boolean',
        'community_profit_share_percentage' => 'decimal:4',
        'community_investment_date' => 'datetime'
    ];

    protected $appends = ['current_value', 'roi'];

    protected static function booted()
    {
        // Temporarily disabled for memory diagnosis
        // static::created(function ($investment) {
        //     if ($investment->status === 'active') {
        //         // Only process referral commissions in web context, not during CLI/seeding
        //         if (!app()->runningInConsole()) {
        //             // Defer referral commission processing to avoid circular dependencies during bootstrap
        //             dispatch(function () use ($investment) {
        //                 if (class_exists(\App\Services\ReferralService::class)) {
        //                     app(\App\Services\ReferralService::class)->processReferralCommission($investment);
        //                 }
        //             })->afterResponse();
        //         }
        //     }
        // });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(InvestmentTier::class, 'tier_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class, 'category_id');
    }

    // Community project relationships
    public function communityProject(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'opportunity_id');
    }

    public function projectContributions(): HasMany
    {
        return $this->hasMany(ProjectContribution::class, 'user_id', 'user_id');
    }

    public function communityInvestmentProfitShares(): HasMany
    {
        return $this->hasMany(CommunityInvestmentProfitShare::class);
    }

    public function investmentOpportunityVotes(): HasMany
    {
        return $this->hasMany(InvestmentOpportunityVote::class);
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'investment_id');
    }

    public function profitShares(): HasMany
    {
        return $this->hasMany(ProfitShare::class, 'investment_id');
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'investment_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function getCurrentTier(): string
    {
        if ($this->amount >= 10000) return 'Elite';
        if ($this->amount >= 5000) return 'Leader';
        if ($this->amount >= 2500) return 'Builder';
        if ($this->amount >= 1000) return 'Starter';
        return 'Basic';
    }

    public function isLockedIn(): bool
    {
        return now()->lt($this->end_date);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // Community project scopes
    public function scopeCommunityInvestments(Builder $query): Builder
    {
        return $query->where('is_community_investment', true);
    }

    public function scopeTraditionalInvestments(Builder $query): Builder
    {
        return $query->where('is_community_investment', false);
    }

    public function scopeEligibleForCommunityProfits(Builder $query): Builder
    {
        return $query->where('eligible_for_community_profits', true);
    }

    public function scopeWithVotingParticipation(Builder $query): Builder
    {
        return $query->where('participated_in_voting', true);
    }

    public function scopeByInvestmentSource(Builder $query, string $source): Builder
    {
        return $query->where('investment_source', $source);
    }

    public function getCurrentValue()
    {
        if (!$this->investment_date) {
            return $this->amount;
        }

        $startDate = Carbon::parse($this->investment_date);
        $duration = $startDate->diffInDays(now());
        
        // Get tier interest rate based on tier name
        $interestRate = match($this->tier) {
            'Basic' => 3.0,
            'Starter' => 5.0,
            'Builder' => 7.0,
            'Leader' => 10.0,
            'Elite' => 15.0,
            default => 0.0
        };
        
        $ratePerDay = $interestRate / 365;
        $interest = ($this->amount * $ratePerDay * $duration) / 100;
        return $this->amount + $interest;
    }

    public function getRoiAttribute()
    {
        if (!isset($this->amount) || $this->amount <= 0) {
            return 0;
        }
        $currentValue = $this->getCurrentValue();
        return number_format((($currentValue - $this->amount) / $this->amount) * 100, 2);
    }

    public function getAverageRoi()
    {
        return $this->getCurrentValue() > 0 ?
            (($this->getCurrentValue() - $this->amount) / $this->amount) * 100 :
            0;
    }

    public function scopeFilterByDateRange($query, $range)
    {
        return match($range) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            'quarter' => $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]),
            'year' => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            default => $query
        };
    }

    protected function getCurrentValueAttribute()
    {
        return $this->getCurrentValue();
    }

    public function getPerformanceMetrics()
    {
        return [
            'roi' => $this->getRoiAttribute(),
            'growth_rate' => $this->getGrowthRate(),
            'volatility' => $this->calculateVolatility(),
            'risk_adjusted_return' => $this->getRiskAdjustedReturn()
        ];
    }

    public function getGrowthRate()
    {
        if (!$this->investment_date) {
            return 0;
        }

        $startDate = Carbon::parse($this->investment_date);
        $days = $startDate->diffInDays(now());
        if ($days === 0) return 0;

        return ($this->getRoiAttribute() / $days) * 30; // Monthly growth rate
    }

    public function calculateVolatility()
    {
        $dailyReturns = $this->referralCommissions()
            ->orderBy('created_at')
            ->pluck('amount')
            ->map(function ($amount) {
                return $amount / $this->amount * 100;
            });

        if ($dailyReturns->isEmpty()) return 0;

        $mean = $dailyReturns->avg();
        $variance = $dailyReturns->map(function ($return) use ($mean) {
            return pow($return - $mean, 2);
        })->avg();

        return sqrt($variance);
    }

    public function getRiskAdjustedReturn()
    {
        $volatility = $this->calculateVolatility();
        if ($volatility === 0) return 0;

        return $this->getRoiAttribute() / $volatility;
    }

    public function calculateNextPaymentDate()
    {
        if (!$this->next_payment_date) {
            $this->next_payment_date = $this->created_at->addDays(30);
            $this->save();
        }
        return $this->next_payment_date;
    }

    public function updateNextPaymentDate()
    {
        $this->next_payment_date = now()->addDays(30);
        $this->save();
        return $this->next_payment_date;
    }

    // VBIF-specific profit calculation methods
    public function calculateFixedAnnualProfitShare(): float
    {
        $tier = $this->tier;
        if (!$tier) {
            return 0;
        }

        return ($this->amount * $tier->fixed_profit_rate) / 100;
    }

    public function calculateProRatedProfitShare(Carbon $fromDate, Carbon $toDate): float
    {
        $tier = $this->tier;
        if (!$tier) {
            return 0;
        }

        $totalDays = $fromDate->diffInDays($toDate);
        $annualProfit = $this->calculateFixedAnnualProfitShare();
        
        return ($annualProfit / 365) * $totalDays;
    }

    public function calculateWeightedAnnualProfit(array $tierHistory): float
    {
        $totalProfit = 0;
        $yearStart = Carbon::create(now()->year, 1, 1);
        $yearEnd = Carbon::create(now()->year, 12, 31);

        foreach ($tierHistory as $index => $tierPeriod) {
            $periodStart = Carbon::parse($tierPeriod['start_date']);
            $periodEnd = isset($tierHistory[$index + 1]) 
                ? Carbon::parse($tierHistory[$index + 1]['start_date']) 
                : $yearEnd;

            // Ensure we're only calculating within the current year
            $periodStart = $periodStart->max($yearStart);
            $periodEnd = $periodEnd->min($yearEnd);

            if ($periodStart->lt($periodEnd)) {
                $tier = InvestmentTier::find($tierPeriod['tier_id']);
                if ($tier) {
                    $periodDays = $periodStart->diffInDays($periodEnd);
                    $annualRate = $tier->fixed_profit_rate;
                    $periodProfit = ($this->amount * $annualRate / 100) * ($periodDays / 365);
                    $totalProfit += $periodProfit;
                }
            }
        }

        return $totalProfit;
    }

    public function calculateTierBasedAnnualProfit(InvestmentTier $specificTier = null): float
    {
        $tier = $specificTier ?? $this->tier;
        if (!$tier) {
            return 0;
        }

        // Apply correct tier percentage based on VBIF requirements
        $profitRate = match($tier->name) {
            'Basic' => 3.0,
            'Starter' => 5.0,
            'Builder' => 7.0,
            'Leader' => 10.0,
            'Elite' => 15.0,
            default => $tier->fixed_profit_rate
        };

        return ($this->amount * $profitRate) / 100;
    }

    public function calculateMonthlyProfitShare(): float
    {
        return $this->calculateFixedAnnualProfitShare() / 12;
    }

    public function calculateDailyProfitShare(): float
    {
        return $this->calculateFixedAnnualProfitShare() / 365;
    }

    public function calculateQuarterlyBonus(float $bonusPool, float $totalInvestmentPool): float
    {
        if ($totalInvestmentPool <= 0) {
            return 0;
        }

        $userPoolPercentage = $this->amount / $totalInvestmentPool;
        return $bonusPool * $userPoolPercentage;
    }

    public function calculatePerformanceBonus(float $fundPerformanceRate, float $bonusPoolPercentage = 7.5): float
    {
        // Calculate bonus based on fund performance (5-10% of profits allocated to bonus pool)
        $quarterlyProfit = $this->amount * ($fundPerformanceRate / 100) / 4;
        return $quarterlyProfit * ($bonusPoolPercentage / 100);
    }

    public function calculateQuarterlyBonusBasedOnFundPerformance(
        float $totalQuarterlyProfit, 
        float $totalInvestmentPool,
        float $bonusPoolAllocation = 7.5
    ): float {
        if ($totalInvestmentPool <= 0 || $totalQuarterlyProfit <= 0) {
            return 0;
        }

        // Allocate 5-10% of total profits to bonus pool (default 7.5%)
        $bonusPool = $totalQuarterlyProfit * ($bonusPoolAllocation / 100);
        
        // Calculate investor's proportional share
        $investorPoolPercentage = $this->amount / $totalInvestmentPool;
        
        return $bonusPool * $investorPoolPercentage;
    }

    public function calculateProportionalQuarterlyBonus(
        float $bonusPool, 
        float $totalInvestmentPool
    ): array {
        if ($totalInvestmentPool <= 0) {
            return [
                'bonus_amount' => 0,
                'pool_percentage' => 0,
                'calculation_details' => 'Invalid investment pool total'
            ];
        }

        $poolPercentage = $this->amount / $totalInvestmentPool;
        $bonusAmount = $bonusPool * $poolPercentage;

        return [
            'bonus_amount' => $bonusAmount,
            'pool_percentage' => $poolPercentage * 100, // Convert to percentage
            'investment_amount' => $this->amount,
            'total_pool' => $totalInvestmentPool,
            'bonus_pool' => $bonusPool,
            'calculation_details' => "({$this->amount} / {$totalInvestmentPool}) * {$bonusPool} = {$bonusAmount}"
        ];
    }

    public function getQuarterlyBonusEligibility(): array
    {
        $lockInStatus = $this->getLockInPeriodRemaining();
        $investmentAge = $this->created_at->diffInMonths(now());

        return [
            'is_eligible' => $this->status === 'active' && !$lockInStatus['is_locked'],
            'investment_age_months' => $investmentAge,
            'lock_in_status' => $lockInStatus,
            'status' => $this->status,
            'eligibility_reason' => $this->getQuarterlyBonusEligibilityReason($lockInStatus)
        ];
    }

    protected function getQuarterlyBonusEligibilityReason(array $lockInStatus): string
    {
        if ($this->status !== 'active') {
            return 'Investment is not active';
        }

        if ($lockInStatus['is_locked']) {
            return 'Investment is still within lock-in period';
        }

        return 'Eligible for quarterly bonus';
    }

    // Lock-in period and withdrawal eligibility methods
    public function isWithinLockInPeriod(): bool
    {
        if (!$this->lock_in_period_end) {
            // Default 12-month lock-in period from investment date
            $lockInEnd = $this->investment_date 
                ? Carbon::parse($this->investment_date)->addYear()
                : $this->created_at->addYear();
            
            return now()->lt($lockInEnd);
        }

        return now()->lt(Carbon::parse($this->lock_in_period_end));
    }

    public function validateLockInPeriod(): array
    {
        $lockInEnd = $this->getLockInEndDate();
        $now = now();
        $isWithinLockIn = $now->lt($lockInEnd);

        return [
            'is_within_lock_in' => $isWithinLockIn,
            'lock_in_end_date' => $lockInEnd,
            'current_date' => $now,
            'days_remaining' => $isWithinLockIn ? $now->diffInDays($lockInEnd) : 0,
            'months_remaining' => $isWithinLockIn ? $now->diffInMonths($lockInEnd) : 0,
            'can_withdraw_without_penalty' => !$isWithinLockIn,
            'minimum_lock_in_period' => 12, // months
            'validation_status' => $isWithinLockIn ? 'locked' : 'unlocked'
        ];
    }

    public function getLockInEndDate(): Carbon
    {
        if ($this->lock_in_period_end) {
            return Carbon::parse($this->lock_in_period_end);
        }

        // Default 12-month lock-in period from investment date
        return $this->investment_date 
            ? Carbon::parse($this->investment_date)->addYear()
            : $this->created_at->addYear();
    }

    public function getLockInPeriodRemaining(): array
    {
        $lockInEnd = $this->lock_in_period_end 
            ? Carbon::parse($this->lock_in_period_end)
            : ($this->investment_date 
                ? Carbon::parse($this->investment_date)->addYear()
                : $this->created_at->addYear());

        $now = now();
        
        if ($now->gte($lockInEnd)) {
            return [
                'is_locked' => false,
                'days_remaining' => 0,
                'months_remaining' => 0,
                'end_date' => $lockInEnd,
                'message' => 'Lock-in period has ended'
            ];
        }

        return [
            'is_locked' => true,
            'days_remaining' => $now->diffInDays($lockInEnd),
            'months_remaining' => $now->diffInMonths($lockInEnd),
            'end_date' => $lockInEnd,
            'message' => 'Investment is still within lock-in period'
        ];
    }

    public function canWithdraw(string $type = 'full'): array
    {
        $lockInStatus = $this->getLockInPeriodRemaining();
        
        if (!$lockInStatus['is_locked']) {
            return [
                'can_withdraw' => true,
                'withdrawal_type' => $type,
                'penalty_amount' => 0,
                'net_amount' => $this->getWithdrawableAmount($type),
                'message' => 'Withdrawal allowed without penalties'
            ];
        }

        // Early withdrawal requires emergency approval and penalties
        $penalties = $this->calculateWithdrawalPenalties();
        
        return [
            'can_withdraw' => $type === 'emergency',
            'withdrawal_type' => $type,
            'requires_approval' => true,
            'penalty_amount' => $penalties['total_penalty'],
            'net_amount' => $this->getWithdrawableAmount($type) - $penalties['total_penalty'],
            'penalty_breakdown' => $penalties,
            'message' => $type === 'emergency' 
                ? 'Emergency withdrawal allowed with penalties'
                : 'Early withdrawal not allowed - emergency approval required'
        ];
    }

    public function isEligibleForWithdrawal(string $withdrawalType = 'full'): array
    {
        $lockInValidation = $this->validateLockInPeriod();
        $currentValue = $this->getCurrentValue();
        $profitAmount = $currentValue - $this->amount;

        // Check basic eligibility criteria
        $eligibilityChecks = [
            'investment_active' => $this->status === 'active',
            'sufficient_balance' => $currentValue > 0,
            'lock_in_period_ended' => !$lockInValidation['is_within_lock_in'],
            'has_profits' => $profitAmount > 0
        ];

        $isEligible = match($withdrawalType) {
            'full' => $eligibilityChecks['investment_active'] && 
                     $eligibilityChecks['sufficient_balance'] &&
                     $eligibilityChecks['lock_in_period_ended'],
            'partial' => $eligibilityChecks['investment_active'] && 
                        $eligibilityChecks['has_profits'] &&
                        $eligibilityChecks['lock_in_period_ended'],
            'emergency' => $eligibilityChecks['investment_active'] && 
                          $eligibilityChecks['sufficient_balance'],
            'profits_only' => $eligibilityChecks['investment_active'] && 
                             $eligibilityChecks['has_profits'] &&
                             $eligibilityChecks['lock_in_period_ended'],
            default => false
        };

        return [
            'is_eligible' => $isEligible,
            'withdrawal_type' => $withdrawalType,
            'eligibility_checks' => $eligibilityChecks,
            'lock_in_validation' => $lockInValidation,
            'current_value' => $currentValue,
            'profit_amount' => $profitAmount,
            'reasons' => $this->getWithdrawalIneligibilityReasons($eligibilityChecks, $withdrawalType)
        ];
    }

    protected function getWithdrawalIneligibilityReasons(array $checks, string $type): array
    {
        $reasons = [];

        if (!$checks['investment_active']) {
            $reasons[] = 'Investment is not active';
        }

        if (!$checks['sufficient_balance']) {
            $reasons[] = 'Insufficient balance for withdrawal';
        }

        if (!$checks['lock_in_period_ended'] && $type !== 'emergency') {
            $reasons[] = 'Investment is still within lock-in period';
        }

        if (!$checks['has_profits'] && in_array($type, ['partial', 'profits_only'])) {
            $reasons[] = 'No profits available for withdrawal';
        }

        return $reasons;
    }

    public function getWithdrawableAmount(string $type = 'full'): float
    {
        $currentValue = $this->getCurrentValue();
        
        return match($type) {
            'full' => $currentValue,
            'partial' => min($currentValue * 0.5, $currentValue - $this->amount), // Max 50% of profits
            'capital' => $this->amount,
            'profits' => $currentValue - $this->amount,
            default => $currentValue
        };
    }

    // Penalty calculation methods based on withdrawal timing
    public function calculateWithdrawalPenalties(): array
    {
        $lockInStatus = $this->getLockInPeriodRemaining();
        
        if (!$lockInStatus['is_locked']) {
            return [
                'profit_penalty_rate' => 0,
                'capital_penalty_rate' => 0,
                'profit_penalty_amount' => 0,
                'capital_penalty_amount' => 0,
                'total_penalty' => 0,
                'penalty_tier' => 'none'
            ];
        }

        $monthsRemaining = $lockInStatus['months_remaining'];
        $currentValue = $this->getCurrentValue();
        $profitAmount = $currentValue - $this->amount;

        // Graduated penalty structure based on remaining lock-in period
        [$profitPenaltyRate, $capitalPenaltyRate, $tier] = $this->getPenaltyRates($monthsRemaining);

        $profitPenalty = $profitAmount * ($profitPenaltyRate / 100);
        $capitalPenalty = $this->amount * ($capitalPenaltyRate / 100);

        return [
            'months_remaining' => $monthsRemaining,
            'penalty_tier' => $tier,
            'profit_penalty_rate' => $profitPenaltyRate,
            'capital_penalty_rate' => $capitalPenaltyRate,
            'profit_penalty_amount' => $profitPenalty,
            'capital_penalty_amount' => $capitalPenalty,
            'total_penalty' => $profitPenalty + $capitalPenalty,
            'net_withdrawable' => $currentValue - ($profitPenalty + $capitalPenalty)
        ];
    }

    protected function getPenaltyRates(int $monthsRemaining): array
    {
        return match(true) {
            $monthsRemaining >= 11 => [100, 12, 'tier_1'], // 0-1 month invested
            $monthsRemaining >= 9 => [100, 12, 'tier_2'],  // 1-3 months invested
            $monthsRemaining >= 6 => [50, 6, 'tier_3'],    // 3-6 months invested
            $monthsRemaining >= 1 => [30, 3, 'tier_4'],    // 6-12 months invested
            default => [0, 0, 'none']                       // After 12 months
        };
    }

    public function calculatePenaltyByWithdrawalTiming(Carbon $withdrawalDate = null): array
    {
        $withdrawalDate = $withdrawalDate ?? now();
        $investmentDate = $this->investment_date 
            ? Carbon::parse($this->investment_date)
            : $this->created_at;

        $monthsInvested = $investmentDate->diffInMonths($withdrawalDate);
        
        return $this->calculateWithdrawalPenalties();
    }

    public function calculateTimedWithdrawalPenalty(Carbon $withdrawalDate = null): array
    {
        $withdrawalDate = $withdrawalDate ?? now();
        $lockInEnd = $this->getLockInEndDate();
        
        if ($withdrawalDate->gte($lockInEnd)) {
            return [
                'penalty_applicable' => false,
                'penalty_amount' => 0,
                'penalty_rate' => 0,
                'withdrawal_timing' => 'after_lock_in',
                'message' => 'No penalty - withdrawal after lock-in period'
            ];
        }

        $monthsRemaining = $withdrawalDate->diffInMonths($lockInEnd);
        $currentValue = $this->getCurrentValue();
        $profitAmount = $currentValue - $this->amount;

        // Apply tier-specific penalty reduction
        $tierReduction = $this->tier ? $this->tier->getWithdrawalPenaltyReduction() : 0;
        
        [$baseProfitRate, $baseCapitalRate, $tier] = $this->getPenaltyRates($monthsRemaining);
        
        // Apply tier reduction
        $adjustedProfitRate = $baseProfitRate * (1 - $tierReduction);
        $adjustedCapitalRate = $baseCapitalRate * (1 - $tierReduction);
        
        $profitPenalty = $profitAmount * ($adjustedProfitRate / 100);
        $capitalPenalty = $this->amount * ($adjustedCapitalRate / 100);
        $totalPenalty = $profitPenalty + $capitalPenalty;

        return [
            'penalty_applicable' => true,
            'withdrawal_date' => $withdrawalDate,
            'lock_in_end_date' => $lockInEnd,
            'months_remaining' => $monthsRemaining,
            'penalty_tier' => $tier,
            'base_profit_penalty_rate' => $baseProfitRate,
            'base_capital_penalty_rate' => $baseCapitalRate,
            'tier_reduction_percentage' => $tierReduction * 100,
            'adjusted_profit_penalty_rate' => $adjustedProfitRate,
            'adjusted_capital_penalty_rate' => $adjustedCapitalRate,
            'profit_penalty_amount' => $profitPenalty,
            'capital_penalty_amount' => $capitalPenalty,
            'total_penalty_amount' => $totalPenalty,
            'net_withdrawable_amount' => $currentValue - $totalPenalty,
            'withdrawal_timing' => 'early_withdrawal'
        ];
    }

    public function getWithdrawalPenaltyStructure(): array
    {
        return [
            'tier_1' => [
                'months_remaining' => '11-12',
                'investment_period' => '0-1 months',
                'profit_penalty_rate' => 100,
                'capital_penalty_rate' => 12,
                'description' => 'Highest penalty for very early withdrawal'
            ],
            'tier_2' => [
                'months_remaining' => '9-10',
                'investment_period' => '1-3 months',
                'profit_penalty_rate' => 100,
                'capital_penalty_rate' => 12,
                'description' => 'High penalty for early withdrawal'
            ],
            'tier_3' => [
                'months_remaining' => '6-8',
                'investment_period' => '3-6 months',
                'profit_penalty_rate' => 50,
                'capital_penalty_rate' => 6,
                'description' => 'Moderate penalty for mid-term withdrawal'
            ],
            'tier_4' => [
                'months_remaining' => '1-5',
                'investment_period' => '6-12 months',
                'profit_penalty_rate' => 30,
                'capital_penalty_rate' => 3,
                'description' => 'Lower penalty for near-maturity withdrawal'
            ],
            'no_penalty' => [
                'months_remaining' => '0',
                'investment_period' => '12+ months',
                'profit_penalty_rate' => 0,
                'capital_penalty_rate' => 0,
                'description' => 'No penalty after lock-in period'
            ]
        ];
    }

    public function simulateWithdrawalScenarios(): array
    {
        $scenarios = [];
        $currentValue = $this->getCurrentValue();
        $lockInEnd = $this->getLockInEndDate();
        
        // Simulate withdrawal at different time points
        $timePoints = [
            ['label' => 'Now', 'date' => now()],
            ['label' => '3 months from now', 'date' => now()->addMonths(3)],
            ['label' => '6 months from now', 'date' => now()->addMonths(6)],
            ['label' => '9 months from now', 'date' => now()->addMonths(9)],
            ['label' => 'At lock-in end', 'date' => $lockInEnd],
            ['label' => '3 months after lock-in', 'date' => $lockInEnd->copy()->addMonths(3)]
        ];

        foreach ($timePoints as $point) {
            $penalty = $this->calculateTimedWithdrawalPenalty($point['date']);
            $scenarios[] = [
                'scenario' => $point['label'],
                'withdrawal_date' => $point['date'],
                'penalty_details' => $penalty,
                'net_amount' => $penalty['penalty_applicable'] 
                    ? $currentValue - $penalty['total_penalty_amount']
                    : $currentValue
            ];
        }

        return $scenarios;
    }

    // Reinvestment and tier upgrade methods
    public function isEligibleForReinvestmentBonus(): bool
    {
        $investmentAge = $this->created_at->diffInYears(now());
        return $investmentAge >= 1; // Eligible after 1 year
    }

    public function calculateReinvestmentBonus(float $reinvestmentAmount): float
    {
        if (!$this->isEligibleForReinvestmentBonus()) {
            return 0;
        }

        $tier = $this->tier;
        if (!$tier || !$tier->reinvestment_bonus_rate) {
            return 0;
        }

        return ($reinvestmentAmount * $tier->reinvestment_bonus_rate) / 100;
    }

    public function getInvestmentPerformanceMetrics(): array
    {
        $currentValue = $this->getCurrentValue();
        $profitAmount = $currentValue - $this->amount;
        $investmentAge = $this->created_at->diffInDays(now());
        
        $annualizedReturn = $investmentAge > 0 
            ? (($currentValue / $this->amount) - 1) * (365 / $investmentAge) * 100
            : 0;

        return [
            'initial_amount' => $this->amount,
            'current_value' => $currentValue,
            'profit_amount' => $profitAmount,
            'roi_percentage' => $this->amount > 0 ? ($profitAmount / $this->amount) * 100 : 0,
            'annualized_return' => $annualizedReturn,
            'investment_age_days' => $investmentAge,
            'tier' => $this->tier?->name,
            'lock_in_status' => $this->getLockInPeriodRemaining(),
            'withdrawal_eligibility' => $this->canWithdraw()
        ];
    }

    public function projectFutureValue(int $months = 12): array
    {
        $tier = $this->tier;
        if (!$tier) {
            return [];
        }

        $monthlyRate = $tier->fixed_profit_rate / 12 / 100;
        $projections = [];

        for ($month = 1; $month <= $months; $month++) {
            $futureValue = $this->amount * (1 + ($tier->fixed_profit_rate / 100) * ($month / 12));
            $projections[] = [
                'month' => $month,
                'projected_value' => $futureValue,
                'projected_profit' => $futureValue - $this->amount,
                'roi_percentage' => (($futureValue - $this->amount) / $this->amount) * 100
            ];
        }

        return $projections;
    }

    // Community Investment Methods
    public function isCommunityInvestment(): bool
    {
        return $this->is_community_investment;
    }

    public function isTraditionalInvestment(): bool
    {
        return !$this->is_community_investment;
    }

    public function isEligibleForCommunityProfits(): bool
    {
        return $this->eligible_for_community_profits;
    }

    public function hasParticipatedInVoting(): bool
    {
        return $this->participated_in_voting;
    }

    public function getCommunityContributionAmount(): float
    {
        return $this->community_contribution_amount ?? 0;
    }

    public function getVotingPowerWeight(): float
    {
        return $this->voting_power_weight ?? 1.0;
    }

    public function getCommunityProfitSharePercentage(): float
    {
        return $this->community_profit_share_percentage ?? 0;
    }

    public function getTierAtCommunityInvestment(): ?string
    {
        return $this->tier_at_community_investment;
    }

    public function getCommunityInvestmentAge(): int
    {
        if (!$this->community_investment_date) {
            return 0;
        }

        return $this->community_investment_date->diffInDays(now());
    }

    public function getCommunityProfitShares(): array
    {
        return $this->communityInvestmentProfitShares()
            ->with('communityInvestmentDistribution')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getTotalCommunityProfitsReceived(): float
    {
        return $this->communityInvestmentProfitShares()
            ->where('payment_status', 'processed')
            ->sum('total_profit_share');
    }

    public function getPendingCommunityProfits(): float
    {
        return $this->communityInvestmentProfitShares()
            ->where('payment_status', 'pending')
            ->sum('total_profit_share');
    }

    public function getCommunityInvestmentSummary(): array
    {
        if (!$this->isCommunityInvestment()) {
            return [];
        }

        return [
            'is_community_investment' => true,
            'investment_source' => $this->investment_source,
            'community_contribution_amount' => $this->getCommunityContributionAmount(),
            'tier_at_investment' => $this->getTierAtCommunityInvestment(),
            'voting_power_weight' => $this->getVotingPowerWeight(),
            'participated_in_voting' => $this->hasParticipatedInVoting(),
            'eligible_for_community_profits' => $this->isEligibleForCommunityProfits(),
            'community_profit_share_percentage' => $this->getCommunityProfitSharePercentage(),
            'community_investment_date' => $this->community_investment_date,
            'investment_age_days' => $this->getCommunityInvestmentAge(),
            'total_community_profits_received' => $this->getTotalCommunityProfitsReceived(),
            'pending_community_profits' => $this->getPendingCommunityProfits(),
            'community_project' => $this->communityProject?->name,
            'investment_opportunity' => $this->investmentOpportunity?->name
        ];
    }

    public function markVotingParticipation(): void
    {
        $this->update(['participated_in_voting' => true]);
    }

    public function updateCommunityProfitEligibility(bool $eligible): void
    {
        $this->update(['eligible_for_community_profits' => $eligible]);
    }

    public function calculateCommunityROI(): float
    {
        if (!$this->isCommunityInvestment() || $this->getCommunityContributionAmount() <= 0) {
            return 0;
        }

        $totalProfits = $this->getTotalCommunityProfitsReceived();
        return ($totalProfits / $this->getCommunityContributionAmount()) * 100;
    }

    public function getCommunityInvestmentMetrics(): array
    {
        if (!$this->isCommunityInvestment()) {
            return [];
        }

        return [
            'community_roi' => $this->calculateCommunityROI(),
            'total_profits_received' => $this->getTotalCommunityProfitsReceived(),
            'pending_profits' => $this->getPendingCommunityProfits(),
            'profit_distributions_count' => $this->communityInvestmentProfitShares()->count(),
            'voting_participation' => $this->hasParticipatedInVoting(),
            'tier_bonus_eligibility' => $this->getTierAtCommunityInvestment(),
            'investment_age_months' => $this->getCommunityInvestmentAge() / 30,
            'effective_voting_power' => $this->getVotingPowerWeight()
        ];
    }
}
