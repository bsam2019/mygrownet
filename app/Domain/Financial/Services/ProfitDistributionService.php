<?php

namespace App\Domain\Financial\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use App\Models\ProfitDistribution;
use App\Models\ProfitShare;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ProfitDistributionService
{
    /**
     * Calculate annual profit distribution for all users
     *
     * @param float $totalFundProfit
     * @param Carbon $distributionDate
     * @return array
     */
    public function calculateAnnualProfitDistribution(float $totalFundProfit, Carbon $distributionDate): array
    {
        $users = User::whereNotNull('current_investment_tier_id')
            ->where('total_investment_amount', '>', 0)
            ->with('currentInvestmentTier')
            ->get();

        $totalInvestmentPool = $users->sum('total_investment_amount');
        $distributions = [];
        $totalDistributed = 0;

        foreach ($users as $user) {
            $distribution = $this->calculateUserAnnualDistribution(
                $user, 
                $totalFundProfit, 
                $totalInvestmentPool, 
                $distributionDate
            );

            if ($distribution['amount'] > 0) {
                $distributions[] = $distribution;
                $totalDistributed += $distribution['amount'];
            }
        }

        return [
            'distribution_date' => $distributionDate,
            'total_fund_profit' => $totalFundProfit,
            'total_investment_pool' => $totalInvestmentPool,
            'total_distributed' => $totalDistributed,
            'distribution_count' => count($distributions),
            'distributions' => $distributions,
            'distribution_percentage' => $totalFundProfit > 0 ? ($totalDistributed / $totalFundProfit) * 100 : 0
        ];
    }

    /**
     * Calculate individual user's annual profit distribution
     *
     * @param User $user
     * @param float $totalFundProfit
     * @param float $totalInvestmentPool
     * @param Carbon $distributionDate
     * @return array
     */
    public function calculateUserAnnualDistribution(
        User $user, 
        float $totalFundProfit, 
        float $totalInvestmentPool, 
        Carbon $distributionDate
    ): array {
        $tier = $user->currentInvestmentTier;
        
        if (!$tier || $user->total_investment_amount <= 0) {
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'amount' => 0,
                'tier' => null,
                'calculation_method' => 'no_tier_or_investment'
            ];
        }

        // Check if user has tier history for weighted calculation
        $tierHistory = $user->getTierHistory();
        $yearStart = $distributionDate->copy()->startOfYear();
        $yearEnd = $distributionDate->copy()->endOfYear();

        if (!empty($tierHistory) && count($tierHistory) > 1) {
            // Use weighted calculation for users with tier changes
            $amount = $this->calculateWeightedAnnualProfit($user, $yearStart, $yearEnd);
            $calculationMethod = 'weighted_tier_history';
        } else {
            // Use fixed rate calculation
            $amount = ($user->total_investment_amount * $tier->fixed_profit_rate) / 100;
            $calculationMethod = 'fixed_tier_rate';
        }

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'amount' => $amount,
            'investment_amount' => $user->total_investment_amount,
            'tier' => $tier->name,
            'tier_rate' => $tier->fixed_profit_rate,
            'pool_percentage' => $totalInvestmentPool > 0 ? ($user->total_investment_amount / $totalInvestmentPool) * 100 : 0,
            'calculation_method' => $calculationMethod,
            'distribution_date' => $distributionDate
        ];
    }

    /**
     * Calculate weighted annual profit for users with tier changes
     *
     * @param User $user
     * @param Carbon $yearStart
     * @param Carbon $yearEnd
     * @return float
     */
    protected function calculateWeightedAnnualProfit(User $user, Carbon $yearStart, Carbon $yearEnd): float
    {
        $tierHistory = $user->getTierHistory();
        $totalProfit = 0;

        foreach ($tierHistory as $index => $tierRecord) {
            $tierStartDate = Carbon::parse($tierRecord['date'])->max($yearStart);
            $tierEndDate = isset($tierHistory[$index + 1]) 
                ? Carbon::parse($tierHistory[$index + 1]['date'])->min($yearEnd)
                : $yearEnd;

            if ($tierStartDate->lt($tierEndDate)) {
                $tier = InvestmentTier::find($tierRecord['tier_id']);
                if ($tier) {
                    $periodDays = $tierStartDate->diffInDays($tierEndDate);
                    $yearDays = $yearStart->diffInDays($yearEnd);
                    $periodRatio = $periodDays / $yearDays;
                    
                    $annualProfit = ($user->total_investment_amount * $tier->fixed_profit_rate) / 100;
                    $totalProfit += $annualProfit * $periodRatio;
                }
            }
        }

        return $totalProfit;
    }

    /**
     * Process annual profit distribution
     *
     * @param float $totalFundProfit
     * @param Carbon $distributionDate
     * @param int $createdBy
     * @return array
     */
    public function processAnnualProfitDistribution(
        float $totalFundProfit, 
        Carbon $distributionDate, 
        int $createdBy
    ): array {
        try {
            DB::beginTransaction();

            $distributionData = $this->calculateAnnualProfitDistribution($totalFundProfit, $distributionDate);
            
            // Create main distribution record
            $distribution = ProfitDistribution::create([
                'period_type' => 'annual',
                'period_start' => $distributionDate->copy()->startOfYear(),
                'period_end' => $distributionDate->copy()->endOfYear(),
                'total_profit' => $totalFundProfit,
                'distribution_percentage' => $distributionData['distribution_percentage'],
                'total_distributed' => $distributionData['total_distributed'],
                'status' => 'completed',
                'created_by' => $createdBy,
                'processed_by' => $createdBy,
                'processed_at' => now(),
                'notes' => json_encode([
                    'total_investment_pool' => $distributionData['total_investment_pool'],
                    'user_count' => $distributionData['distribution_count'],
                    'calculation_method' => 'tier_based_fixed_rates'
                ])
            ]);

            $processedShares = [];
            
            // Create individual profit shares
            foreach ($distributionData['distributions'] as $userDistribution) {
                $profitShare = ProfitShare::create([
                    'user_id' => $userDistribution['user_id'],
                    'distribution_id' => $distribution->id,
                    'amount' => $userDistribution['amount'],
                    'investment_amount' => $userDistribution['investment_amount'],
                    'tier_name' => $userDistribution['tier'],
                    'tier_rate' => $userDistribution['tier_rate'],
                    'pool_percentage' => $userDistribution['pool_percentage'],
                    'calculation_method' => $userDistribution['calculation_method'],
                    'status' => 'pending',
                    'distribution_date' => $distributionDate
                ]);

                // Create transaction record
                Transaction::create([
                    'user_id' => $userDistribution['user_id'],
                    'type' => 'profit_distribution',
                    'amount' => $userDistribution['amount'],
                    'status' => 'pending',
                    'description' => "Annual profit distribution for {$distributionDate->year}",
                    'reference_id' => $profitShare->id,
                    'reference_type' => 'profit_share'
                ]);

                $processedShares[] = [
                    'user_id' => $userDistribution['user_id'],
                    'amount' => $userDistribution['amount'],
                    'profit_share_id' => $profitShare->id
                ];

                // Record activity
                $user = User::find($userDistribution['user_id']);
                $user?->recordActivity(
                    'profit_distribution_received',
                    "Annual profit distribution: K{$userDistribution['amount']} for {$distributionDate->year}"
                );
            }

            DB::commit();

            Log::info("Processed annual profit distribution: K{$distributionData['total_distributed']} to {$distributionData['distribution_count']} users");

            return [
                'success' => true,
                'distribution_id' => $distribution->id,
                'total_distributed' => $distributionData['total_distributed'],
                'user_count' => $distributionData['distribution_count'],
                'processed_shares' => $processedShares
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process annual profit distribution: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'distribution_id' => null,
                'total_distributed' => 0,
                'user_count' => 0
            ];
        }
    }

    /**
     * Calculate quarterly bonus pool allocation
     *
     * @param float $totalQuarterlyProfit
     * @param float $bonusPoolPercentage
     * @param Carbon $quarterDate
     * @return array
     */
    public function calculateQuarterlyBonusPool(
        float $totalQuarterlyProfit, 
        float $bonusPoolPercentage = 7.5, 
        Carbon $quarterDate = null
    ): array {
        $quarterDate = $quarterDate ?? now();
        $quarterStart = $quarterDate->copy()->startOfQuarter();
        $quarterEnd = $quarterDate->copy()->endOfQuarter();

        // Calculate bonus pool (5-10% of quarterly profits, default 7.5%)
        $bonusPool = $totalQuarterlyProfit * ($bonusPoolPercentage / 100);

        // Get all active users with investments
        $users = User::whereNotNull('current_investment_tier_id')
            ->where('total_investment_amount', '>', 0)
            ->with('currentInvestmentTier')
            ->get();

        $totalInvestmentPool = $users->sum('total_investment_amount');
        $bonusAllocations = [];
        $totalAllocated = 0;

        foreach ($users as $user) {
            $allocation = $this->calculateUserQuarterlyBonus(
                $user, 
                $bonusPool, 
                $totalInvestmentPool, 
                $quarterStart, 
                $quarterEnd
            );

            if ($allocation['amount'] > 0) {
                $bonusAllocations[] = $allocation;
                $totalAllocated += $allocation['amount'];
            }
        }

        return [
            'quarter' => $quarterDate->format('Y-Q'),
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd,
            'total_quarterly_profit' => $totalQuarterlyProfit,
            'bonus_pool_percentage' => $bonusPoolPercentage,
            'bonus_pool' => $bonusPool,
            'total_investment_pool' => $totalInvestmentPool,
            'total_allocated' => $totalAllocated,
            'allocation_count' => count($bonusAllocations),
            'allocations' => $bonusAllocations,
            'remaining_pool' => $bonusPool - $totalAllocated
        ];
    }

    /**
     * Calculate individual user's quarterly bonus
     *
     * @param User $user
     * @param float $bonusPool
     * @param float $totalInvestmentPool
     * @param Carbon $quarterStart
     * @param Carbon $quarterEnd
     * @return array
     */
    public function calculateUserQuarterlyBonus(
        User $user, 
        float $bonusPool, 
        float $totalInvestmentPool, 
        Carbon $quarterStart, 
        Carbon $quarterEnd
    ): array {
        // Check if user is eligible for quarterly bonus
        $eligibility = $this->checkQuarterlyBonusEligibility($user, $quarterStart, $quarterEnd);
        
        if (!$eligibility['is_eligible']) {
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'amount' => 0,
                'pool_percentage' => 0,
                'eligibility_status' => $eligibility['reason']
            ];
        }

        // Calculate proportional share
        $poolPercentage = $totalInvestmentPool > 0 
            ? ($user->total_investment_amount / $totalInvestmentPool) * 100 
            : 0;
        
        $bonusAmount = $bonusPool * ($poolPercentage / 100);

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'amount' => $bonusAmount,
            'investment_amount' => $user->total_investment_amount,
            'pool_percentage' => $poolPercentage,
            'tier' => $user->currentInvestmentTier?->name,
            'eligibility_status' => 'eligible',
            'quarter_start' => $quarterStart,
            'quarter_end' => $quarterEnd
        ];
    }

    /**
     * Check quarterly bonus eligibility
     *
     * @param User $user
     * @param Carbon $quarterStart
     * @param Carbon $quarterEnd
     * @return array
     */
    protected function checkQuarterlyBonusEligibility(User $user, Carbon $quarterStart, Carbon $quarterEnd): array
    {
        // Check if user has active investments
        if ($user->total_investment_amount <= 0) {
            return [
                'is_eligible' => false,
                'reason' => 'no_active_investment'
            ];
        }

        // Check if user has any investments within lock-in period
        $activeInvestments = Investment::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $hasLockedInvestments = false;
        foreach ($activeInvestments as $investment) {
            if ($investment->isWithinLockInPeriod()) {
                $hasLockedInvestments = true;
                break;
            }
        }

        if ($hasLockedInvestments) {
            return [
                'is_eligible' => false,
                'reason' => 'investments_within_lock_in_period'
            ];
        }

        // Check if user was active during the quarter
        $userCreatedInQuarter = $user->created_at->between($quarterStart, $quarterEnd);
        $hasInvestmentActivity = Investment::where('user_id', $user->id)
            ->whereBetween('created_at', [$quarterStart, $quarterEnd])
            ->exists();

        if (!$userCreatedInQuarter && !$hasInvestmentActivity && $user->created_at->gt($quarterEnd)) {
            return [
                'is_eligible' => false,
                'reason' => 'not_active_during_quarter'
            ];
        }

        return [
            'is_eligible' => true,
            'reason' => 'eligible'
        ];
    }

    /**
     * Process quarterly bonus distribution
     *
     * @param float $totalQuarterlyProfit
     * @param float $bonusPoolPercentage
     * @param Carbon $quarterDate
     * @param int $createdBy
     * @return array
     */
    public function processQuarterlyBonusDistribution(
        float $totalQuarterlyProfit, 
        float $bonusPoolPercentage, 
        Carbon $quarterDate, 
        int $createdBy
    ): array {
        try {
            DB::beginTransaction();

            $bonusData = $this->calculateQuarterlyBonusPool($totalQuarterlyProfit, $bonusPoolPercentage, $quarterDate);
            
            // Create main distribution record
            $distribution = ProfitDistribution::create([
                'period_type' => 'quarterly',
                'period_start' => $bonusData['quarter_start'],
                'period_end' => $bonusData['quarter_end'],
                'total_profit' => $totalQuarterlyProfit,
                'distribution_percentage' => $bonusPoolPercentage,
                'total_distributed' => $bonusData['total_allocated'],
                'status' => 'completed',
                'created_by' => $createdBy,
                'processed_by' => $createdBy,
                'processed_at' => now(),
                'notes' => json_encode([
                    'quarter' => $bonusData['quarter'],
                    'bonus_pool' => $bonusData['bonus_pool'],
                    'remaining_pool' => $bonusData['remaining_pool'],
                    'total_investment_pool' => $bonusData['total_investment_pool'],
                    'user_count' => $bonusData['allocation_count']
                ])
            ]);

            $processedBonuses = [];
            
            // Create individual bonus shares
            foreach ($bonusData['allocations'] as $userBonus) {
                $profitShare = ProfitShare::create([
                    'user_id' => $userBonus['user_id'],
                    'distribution_id' => $distribution->id,
                    'amount' => $userBonus['amount'],
                    'investment_amount' => $userBonus['investment_amount'],
                    'tier_name' => $userBonus['tier'],
                    'pool_percentage' => $userBonus['pool_percentage'],
                    'calculation_method' => 'quarterly_bonus_pool',
                    'status' => 'pending',
                    'distribution_date' => $quarterDate
                ]);

                // Create transaction record
                Transaction::create([
                    'user_id' => $userBonus['user_id'],
                    'type' => 'quarterly_bonus',
                    'amount' => $userBonus['amount'],
                    'status' => 'pending',
                    'description' => "Quarterly bonus for {$bonusData['quarter']}",
                    'reference_id' => $profitShare->id,
                    'reference_type' => 'profit_share'
                ]);

                $processedBonuses[] = [
                    'user_id' => $userBonus['user_id'],
                    'amount' => $userBonus['amount'],
                    'profit_share_id' => $profitShare->id
                ];

                // Record activity
                $user = User::find($userBonus['user_id']);
                $user?->recordActivity(
                    'quarterly_bonus_received',
                    "Quarterly bonus: K{$userBonus['amount']} for {$bonusData['quarter']}"
                );
            }

            DB::commit();

            Log::info("Processed quarterly bonus distribution: K{$bonusData['total_allocated']} to {$bonusData['allocation_count']} users for {$bonusData['quarter']}");

            return [
                'success' => true,
                'distribution_id' => $distribution->id,
                'quarter' => $bonusData['quarter'],
                'total_distributed' => $bonusData['total_allocated'],
                'user_count' => $bonusData['allocation_count'],
                'bonus_pool' => $bonusData['bonus_pool'],
                'remaining_pool' => $bonusData['remaining_pool'],
                'processed_bonuses' => $processedBonuses
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process quarterly bonus distribution: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'distribution_id' => null,
                'total_distributed' => 0,
                'user_count' => 0
            ];
        }
    }

    /**
     * Calculate investor pool percentage for a user
     *
     * @param User $user
     * @param float $totalInvestmentPool
     * @return array
     */
    public function calculateInvestorPoolPercentage(User $user, float $totalInvestmentPool = null): array
    {
        if ($totalInvestmentPool === null) {
            $totalInvestmentPool = User::whereNotNull('current_investment_tier_id')
                ->where('total_investment_amount', '>', 0)
                ->sum('total_investment_amount');
        }

        $userInvestment = $user->total_investment_amount;
        $poolPercentage = $totalInvestmentPool > 0 ? ($userInvestment / $totalInvestmentPool) * 100 : 0;

        return [
            'user_id' => $user->id,
            'user_investment' => $userInvestment,
            'total_pool' => $totalInvestmentPool,
            'pool_percentage' => $poolPercentage,
            'tier' => $user->currentInvestmentTier?->name,
            'calculation' => "({$userInvestment} / {$totalInvestmentPool}) * 100 = {$poolPercentage}%"
        ];
    }

    /**
     * Record profit distribution transaction
     *
     * @param ProfitShare $profitShare
     * @return bool
     */
    public function recordProfitDistributionTransaction(ProfitShare $profitShare): bool
    {
        try {
            DB::beginTransaction();

            // Update profit share status
            $profitShare->update([
                'status' => 'paid',
                'paid_at' => now()
            ]);

            // Update transaction status
            $transaction = Transaction::where('reference_id', $profitShare->id)
                ->where('reference_type', 'profit_share')
                ->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'processed_at' => now()
                ]);
            }

            // Update user's total earnings
            $user = $profitShare->user;
            $user->increment('total_profit_earnings', $profitShare->amount);

            // Record activity
            $user->recordActivity(
                'profit_distribution_paid',
                "Profit distribution paid: K{$profitShare->amount}"
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to record profit distribution transaction for share {$profitShare->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get profit distribution statistics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getProfitDistributionStatistics(Carbon $startDate = null, Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfYear();
        $endDate = $endDate ?? now()->endOfYear();

        $distributions = ProfitDistribution::where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('period_start', [$startDate, $endDate])
                      ->orWhereBetween('period_end', [$startDate, $endDate]);
            })
            ->get();

        $totalDistributed = $distributions->sum('total_distributed');
        $averageDistribution = $distributions->count() > 0 ? $totalDistributed / $distributions->count() : 0;

        $byType = $distributions->groupBy('period_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('total_distributed'),
                'average_amount' => $group->avg('total_distributed')
            ];
        });

        $monthlyBreakdown = $distributions->groupBy(function ($distribution) {
            return $distribution->period_start->format('Y-m');
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_amount' => $group->sum('total_distributed')
            ];
        });

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_distributions' => $distributions->count(),
                'total_amount_distributed' => $totalDistributed,
                'average_distribution_amount' => $averageDistribution
            ],
            'by_type' => $byType,
            'monthly_breakdown' => $monthlyBreakdown,
            'recent_distributions' => $distributions->sortByDesc('period_start')
                ->take(5)
                ->map(function ($distribution) {
                    $notes = json_decode($distribution->notes, true) ?? [];
                    return [
                        'id' => $distribution->id,
                        'type' => $distribution->period_type,
                        'amount' => $distribution->total_distributed,
                        'user_count' => $notes['user_count'] ?? 0,
                        'date' => $distribution->period_start,
                        'status' => $distribution->status
                    ];
                })
                ->values()
        ];
    }
}