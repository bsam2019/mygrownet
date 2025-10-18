<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitCalculationService
{
    /**
     * Calculate monthly profit for a user's investment
     *
     * @param User $user
     * @param Investment $investment
     * @return float
     */
    public function calculateMonthlyProfit(User $user, Investment $investment): float
    {
        $tier = $investment->tier;
        $baseProfit = $tier->calculateMonthlyProfit($investment->amount);
        
        // Add performance bonus if applicable
        if ($tier->settings && $tier->settings->performance_bonus_rate) {
            $bonus = $tier->settings->calculatePerformanceBonus($baseProfit);
            $baseProfit += $bonus;
        }

        return $baseProfit;
    }

    /**
     * Calculate quarterly profit pool
     *
     * @return float
     */
    public function calculateQuarterlyProfitPool(): float
    {
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();

        return Investment::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') * 0.15; // 15% of total investments for the quarter
    }

    /**
     * Calculate individual share of quarterly profit pool
     *
     * @param User $user
     * @return float
     */
    public function calculateQuarterlyProfitShare(User $user): float
    {
        $quarterlyPool = $this->calculateQuarterlyProfitPool();
        $totalInvestments = Investment::where('user_id', $user->id)->sum('amount');
        $totalSystemInvestments = Investment::sum('amount');

        if ($totalSystemInvestments == 0) {
            return 0;
        }

        return ($totalInvestments / $totalSystemInvestments) * $quarterlyPool;
    }

    /**
     * Calculate referral commission
     *
     * @param User $referrer
     * @param Investment $investment
     * @param int $level
     * @return float
     */
    public function calculateReferralCommission(User $referrer, Investment $investment, int $level = 1): float
    {
        $tier = $referrer->currentInvestmentTier;
        if (!$tier) {
            return 0;
        }

        return $tier->calculateReferralCommission($investment->amount, $level);
    }

    /**
     * Calculate total earnings for a user
     *
     * @param User $user
     * @return array
     */
    public function calculateTotalEarnings(User $user): array
    {
        $investments = $user->investments;
        $totalInvestmentAmount = $investments->sum('amount');
        $totalMonthlyProfit = 0;
        $totalReferralEarnings = 0;

        foreach ($investments as $investment) {
            $totalMonthlyProfit += $this->calculateMonthlyProfit($user, $investment);
        }

        // Calculate referral earnings
        $referralCommissions = $user->referralCommissions;
        foreach ($referralCommissions as $commission) {
            $totalReferralEarnings += $commission->amount;
        }

        // Calculate quarterly profit share
        $quarterlyShare = $this->calculateQuarterlyProfitShare($user);

        return [
            'total_investment' => $totalInvestmentAmount,
            'monthly_profit' => $totalMonthlyProfit,
            'referral_earnings' => $totalReferralEarnings,
            'quarterly_share' => $quarterlyShare,
            'total_earnings' => $totalMonthlyProfit + $totalReferralEarnings + $quarterlyShare
        ];
    }

    /**
     * Check if a withdrawal is allowed based on tier settings
     *
     * @param User $user
     * @param float $amount
     * @return array
     */
    public function isWithdrawalAllowed(User $user, float $amount): array
    {
        $tier = $user->currentInvestmentTier;
        if (!$tier || !$tier->settings) {
            return [
                'allowed' => false,
                'reason' => 'No active investment tier found'
            ];
        }

        $earnings = $this->calculateTotalEarnings($user);
        $totalWithdrawable = $earnings['total_earnings'];

        // Check if within lock-in period
        $oldestInvestment = $user->investments()->oldest()->first();
        if ($oldestInvestment && !$tier->settings->isWithinLockInPeriod($oldestInvestment->created_at)) {
            return [
                'allowed' => false,
                'reason' => 'Investment is still within lock-in period'
            ];
        }

        // Check partial withdrawal limit
        $maxWithdrawal = $tier->settings->calculatePartialWithdrawalLimit($totalWithdrawable);
        if ($amount > $maxWithdrawal) {
            return [
                'allowed' => false,
                'reason' => "Amount exceeds maximum withdrawal limit of $maxWithdrawal"
            ];
        }

        return [
            'allowed' => true,
            'penalty' => 0 // No penalty if withdrawal is allowed
        ];
    }

    /**
     * Calculate early withdrawal penalty
     *
     * @param User $user
     * @param float $amount
     * @return float
     */
    public function calculateEarlyWithdrawalPenalty(User $user, float $amount): float
    {
        $tier = $user->currentInvestmentTier;
        if (!$tier || !$tier->settings) {
            return 0;
        }

        return $tier->settings->calculateEarlyWithdrawalPenalty($amount);
    }

    /**
     * Process profit distribution for all users
     *
     * @return array
     */
    public function processProfitDistribution(): array
    {
        $users = User::whereNotNull('current_investment_tier_id')->get();
        $totalDistributed = 0;
        $processedUsers = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                $earnings = $this->calculateTotalEarnings($user);
                $monthlyProfit = $earnings['monthly_profit'];
                
                if ($monthlyProfit > 0) {
                    // Create profit transaction
                    $user->profitTransactions()->create([
                        'amount' => $monthlyProfit,
                        'type' => 'monthly_profit',
                        'status' => 'completed',
                        'description' => 'Monthly profit from investment tier'
                    ]);
                    
                    // Update user's total earnings
                    $user->increment('total_earnings', $monthlyProfit);
                    $user->increment('total_profit_earnings', $monthlyProfit);
                    
                    $totalDistributed += $monthlyProfit;
                }
                
                $processedUsers++;
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'total_distributed' => $totalDistributed,
                'processed_users' => $processedUsers
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process quarterly profit distribution
     *
     * @return array
     */
    public function processQuarterlyProfitDistribution(): array
    {
        $users = User::whereNotNull('current_investment_tier_id')->get();
        $totalDistributed = 0;
        $processedUsers = 0;

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                $quarterlyShare = $this->calculateQuarterlyProfitShare($user);
                
                if ($quarterlyShare > 0) {
                    // Create profit transaction
                    $user->profitTransactions()->create([
                        'amount' => $quarterlyShare,
                        'type' => 'quarterly_share',
                        'status' => 'completed',
                        'description' => 'Quarterly profit share from investment pool'
                    ]);
                    
                    // Update user's total earnings
                    $user->increment('total_earnings', $quarterlyShare);
                    $user->increment('total_profit_earnings', $quarterlyShare);
                    
                    $totalDistributed += $quarterlyShare;
                }
                
                $processedUsers++;
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'total_distributed' => $totalDistributed,
                'processed_users' => $processedUsers
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 