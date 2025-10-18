<?php

namespace App\Domain\Investment\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestmentTierService
{
    /**
     * Calculate the appropriate tier for a given investment amount
     *
     * @param float $amount
     * @return InvestmentTier|null
     */
    public function calculateTierForAmount(float $amount): ?InvestmentTier
    {
        return InvestmentTier::active()
            ->where('minimum_investment', '<=', $amount)
            ->orderBy('minimum_investment', 'desc')
            ->first();
    }

    /**
     * Check if user is eligible for tier upgrade and perform upgrade if eligible
     *
     * @param User $user
     * @return bool
     */
    public function upgradeTierIfEligible(User $user): bool
    {
        $eligibility = $user->checkTierUpgradeEligibility();
        
        if (!$eligibility['eligible'] || !$eligibility['next_tier']) {
            return false;
        }

        return $this->performTierUpgrade($user, $eligibility['next_tier']);
    }

    /**
     * Perform tier upgrade with history tracking
     *
     * @param User $user
     * @param InvestmentTier $newTier
     * @param string $reason
     * @return bool
     */
    public function performTierUpgrade(User $user, InvestmentTier $newTier, string $reason = 'Automatic upgrade based on investment amount'): bool
    {
        try {
            DB::beginTransaction();

            $oldTier = $user->currentInvestmentTier;
            
            // Update user's tier
            $user->current_investment_tier_id = $newTier->id;
            $user->tier_upgraded_at = now();
            
            // Add to tier history
            $user->addTierHistory($newTier->id, $reason);
            $user->save();

            // Record activity
            $user->recordActivity(
                'tier_upgraded',
                "Upgraded from {$oldTier?->name} to {$newTier->name}: {$reason}"
            );

            DB::commit();
            
            Log::info("User {$user->id} upgraded from tier {$oldTier?->name} to {$newTier->name}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to upgrade user {$user->id} tier: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tier benefits for display
     *
     * @param InvestmentTier $tier
     * @return array
     */
    public function getTierBenefits(InvestmentTier $tier): array
    {
        return [
            'tier_name' => $tier->name,
            'minimum_investment' => $tier->minimum_investment,
            'fixed_profit_rate' => $tier->fixed_profit_rate,
            'referral_rates' => [
                'level_1' => $tier->direct_referral_rate,
                'level_2' => $tier->level2_referral_rate,
                'level_3' => $tier->level3_referral_rate,
            ],
            'reinvestment_bonus_rate' => $tier->reinvestment_bonus_rate,
            'enhanced_year2_rate' => $tier->getEnhancedProfitRateForReinvestment(),
            'withdrawal_penalty_reduction' => $tier->getWithdrawalPenaltyReduction(),
            'matrix_spillover_priority' => $tier->getMatrixSpilloverPriority(),
            'max_referral_levels' => $tier->getMaxReferralLevels(),
            'matrix_commission_structure' => $tier->getMatrixCommissionStructure(),
            'benefits' => $tier->benefits ?? [],
        ];
    }

    /**
     * Calculate profit share for annual distributions
     *
     * @param User $user
     * @param float $totalProfit
     * @return float
     */
    public function calculateProfitShare(User $user, float $totalProfit): float
    {
        $tier = $user->currentInvestmentTier;
        if (!$tier) {
            return 0;
        }

        $userInvestmentAmount = $user->total_investment_amount;
        if ($userInvestmentAmount <= 0) {
            return 0;
        }

        // Calculate fixed annual profit share based on tier
        return ($userInvestmentAmount * $tier->fixed_profit_rate) / 100;
    }

    /**
     * Calculate weighted annual profit for users with tier changes during the year
     *
     * @param User $user
     * @param Carbon $yearStart
     * @param Carbon $yearEnd
     * @return float
     */
    public function calculateWeightedAnnualProfit(User $user, Carbon $yearStart, Carbon $yearEnd): float
    {
        $tierHistory = $user->getTierHistory();
        
        if (empty($tierHistory)) {
            // No tier history, use current tier for full year
            return $this->calculateProfitShare($user, 0);
        }

        $totalProfit = 0;
        $currentDate = $yearStart->copy();

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
     * Calculate tier upgrade requirements for a user
     *
     * @param User $user
     * @return array
     */
    public function getTierUpgradeRequirements(User $user): array
    {
        $currentTier = $user->currentInvestmentTier;
        $totalInvestment = $user->total_investment_amount;

        if (!$currentTier) {
            $firstTier = InvestmentTier::active()->ordered()->first();
            return [
                'has_tier' => false,
                'current_tier' => null,
                'next_tier' => $firstTier,
                'required_amount' => $firstTier?->minimum_investment ?? 0,
                'current_amount' => $totalInvestment,
                'remaining_amount' => max(0, ($firstTier?->minimum_investment ?? 0) - $totalInvestment),
                'progress_percentage' => 0,
                'can_upgrade' => false
            ];
        }

        $nextTier = $currentTier->getNextTier();
        if (!$nextTier) {
            return [
                'has_tier' => true,
                'current_tier' => $currentTier,
                'next_tier' => null,
                'required_amount' => 0,
                'current_amount' => $totalInvestment,
                'remaining_amount' => 0,
                'progress_percentage' => 100,
                'can_upgrade' => false,
                'message' => 'Already at highest tier'
            ];
        }

        $remainingAmount = max(0, $nextTier->minimum_investment - $totalInvestment);
        $progressPercentage = $this->calculateTierProgress($currentTier, $nextTier, $totalInvestment);

        return [
            'has_tier' => true,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'required_amount' => $nextTier->minimum_investment,
            'current_amount' => $totalInvestment,
            'remaining_amount' => $remainingAmount,
            'progress_percentage' => $progressPercentage,
            'can_upgrade' => $remainingAmount <= 0,
            'upgrade_benefits' => $currentTier->getUpgradeBenefits()
        ];
    }

    /**
     * Calculate progress percentage towards next tier
     *
     * @param InvestmentTier $currentTier
     * @param InvestmentTier $nextTier
     * @param float $currentAmount
     * @return float
     */
    protected function calculateTierProgress(InvestmentTier $currentTier, InvestmentTier $nextTier, float $currentAmount): float
    {
        $currentTierMin = $currentTier->minimum_investment;
        $nextTierMin = $nextTier->minimum_investment;

        if ($nextTierMin <= $currentTierMin) {
            return 100;
        }

        $progress = ($currentAmount - $currentTierMin) / ($nextTierMin - $currentTierMin);
        return min(100, max(0, $progress * 100));
    }

    /**
     * Get all available tiers with their benefits
     *
     * @return array
     */
    public function getAllTiersWithBenefits(): array
    {
        $tiers = InvestmentTier::active()->ordered()->get();
        
        return $tiers->map(function ($tier) {
            return $this->getTierBenefits($tier);
        })->toArray();
    }

    /**
     * Calculate potential earnings for different tiers
     *
     * @param float $investmentAmount
     * @return array
     */
    public function calculateTierEarningsComparison(float $investmentAmount): array
    {
        $tiers = InvestmentTier::active()->ordered()->get();
        $comparisons = [];

        foreach ($tiers as $tier) {
            $annualProfit = ($investmentAmount * $tier->fixed_profit_rate) / 100;
            $monthlyProfit = $annualProfit / 12;
            
            // Calculate potential referral earnings (assuming 3 direct referrals)
            $directReferralEarnings = 3 * ($investmentAmount * ($tier->direct_referral_rate ?? 0)) / 100;
            
            // Calculate matrix earnings potential
            $matrixEarnings = $tier->calculateMaxMatrixEarnings($investmentAmount);

            $comparisons[] = [
                'tier' => $tier->name,
                'minimum_investment' => $tier->minimum_investment,
                'annual_profit' => $annualProfit,
                'monthly_profit' => $monthlyProfit,
                'profit_rate' => $tier->fixed_profit_rate,
                'direct_referral_rate' => $tier->direct_referral_rate ?? 0,
                'potential_referral_earnings' => $directReferralEarnings,
                'matrix_earnings_potential' => $matrixEarnings,
                'total_potential_annual' => $annualProfit + $directReferralEarnings,
                'reinvestment_bonus_rate' => $tier->reinvestment_bonus_rate ?? 0,
                'enhanced_year2_rate' => $tier->getEnhancedProfitRateForReinvestment()
            ];
        }

        return $comparisons;
    }

    /**
     * Process automatic tier upgrades for all eligible users
     *
     * @return array
     */
    public function processAutomaticTierUpgrades(): array
    {
        $users = User::whereNotNull('total_investment_amount')
            ->where('total_investment_amount', '>', 0)
            ->with('currentInvestmentTier')
            ->get();

        $upgradedCount = 0;
        $processedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $processedCount++;
                
                if ($this->upgradeTierIfEligible($user)) {
                    $upgradedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
                Log::error("Failed to process tier upgrade for user {$user->id}: " . $e->getMessage());
            }
        }

        return [
            'processed_count' => $processedCount,
            'upgraded_count' => $upgradedCount,
            'errors' => $errors
        ];
    }

    /**
     * Calculate tier-based annual profit distribution
     *
     * @param User $user
     * @param float $totalFundProfit
     * @param float $totalInvestmentPool
     * @return array
     */
    public function calculateAnnualProfitDistribution(User $user, float $totalFundProfit, float $totalInvestmentPool): array
    {
        $tier = $user->currentInvestmentTier;
        if (!$tier || $user->total_investment_amount <= 0) {
            return [
                'fixed_profit_share' => 0,
                'user_pool_percentage' => 0,
                'tier_rate' => 0,
                'calculation_details' => 'No active tier or investment'
            ];
        }

        // Fixed profit share based on tier rate
        $fixedProfitShare = ($user->total_investment_amount * $tier->fixed_profit_rate) / 100;
        
        // User's percentage of total investment pool
        $userPoolPercentage = $totalInvestmentPool > 0 
            ? ($user->total_investment_amount / $totalInvestmentPool) * 100 
            : 0;

        return [
            'fixed_profit_share' => $fixedProfitShare,
            'user_pool_percentage' => $userPoolPercentage,
            'tier_rate' => $tier->fixed_profit_rate,
            'tier_name' => $tier->name,
            'investment_amount' => $user->total_investment_amount,
            'calculation_details' => "({$user->total_investment_amount} * {$tier->fixed_profit_rate}%) = {$fixedProfitShare}"
        ];
    }

    /**
     * Get tier statistics for admin dashboard
     *
     * @return array
     */
    public function getTierStatistics(): array
    {
        $tiers = InvestmentTier::active()->ordered()->get();
        $statistics = [];

        foreach ($tiers as $tier) {
            $userCount = User::where('current_investment_tier_id', $tier->id)->count();
            $totalInvestments = User::where('current_investment_tier_id', $tier->id)
                ->sum('total_investment_amount');
            
            $avgInvestment = $userCount > 0 ? $totalInvestments / $userCount : 0;

            $statistics[] = [
                'tier_name' => $tier->name,
                'user_count' => $userCount,
                'total_investments' => $totalInvestments,
                'average_investment' => $avgInvestment,
                'minimum_investment' => $tier->minimum_investment,
                'profit_rate' => $tier->fixed_profit_rate,
                'market_share_percentage' => 0 // Will be calculated after all tiers
            ];
        }

        // Calculate market share percentages
        $totalSystemInvestments = collect($statistics)->sum('total_investments');
        
        if ($totalSystemInvestments > 0) {
            foreach ($statistics as &$stat) {
                $stat['market_share_percentage'] = ($stat['total_investments'] / $totalSystemInvestments) * 100;
            }
        }

        return [
            'tier_breakdown' => $statistics,
            'total_system_investments' => $totalSystemInvestments,
            'total_users_with_tiers' => collect($statistics)->sum('user_count'),
            'average_system_investment' => collect($statistics)->avg('average_investment')
        ];
    }

    /**
     * Validate tier eligibility for investment amount
     *
     * @param float $amount
     * @param int|null $tierid
     * @return array
     */
    public function validateTierEligibility(float $amount, ?int $tierId = null): array
    {
        if ($tierId) {
            $tier = InvestmentTier::find($tierId);
            if (!$tier) {
                return [
                    'valid' => false,
                    'message' => 'Invalid tier selected',
                    'suggested_tier' => null
                ];
            }

            if ($amount < $tier->minimum_investment) {
                $suggestedTier = $this->calculateTierForAmount($amount);
                return [
                    'valid' => false,
                    'message' => "Investment amount ({$amount}) is below minimum for {$tier->name} tier ({$tier->minimum_investment})",
                    'suggested_tier' => $suggestedTier
                ];
            }

            return [
                'valid' => true,
                'tier' => $tier,
                'message' => 'Tier eligibility confirmed'
            ];
        }

        // Auto-calculate tier
        $suggestedTier = $this->calculateTierForAmount($amount);
        
        if (!$suggestedTier) {
            return [
                'valid' => false,
                'message' => 'Investment amount is below minimum tier requirement',
                'suggested_tier' => null
            ];
        }

        return [
            'valid' => true,
            'tier' => $suggestedTier,
            'message' => 'Tier automatically calculated based on investment amount'
        ];
    }
}