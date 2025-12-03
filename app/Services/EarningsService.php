<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Centralized Earnings Service
 * 
 * Handles all earnings calculations for easy maintenance and consistency
 */
class EarningsService
{
    /**
     * Calculate total earnings for a user from ALL sources
     * 
     * NOTE: getCommissionEarnings() already includes ALL commission types
     * (registration, starter_kit, subscription, product_purchase, etc.)
     * So we DON'T add getStarterKitEarnings(), getSubscriptionEarnings(), etc.
     * separately to avoid double-counting!
     * 
     * @param User $user
     * @return float
     */
    public function calculateTotalEarnings(User $user): float
    {
        return $this->getCommissionEarnings($user) 
             + $this->getProfitShareEarnings($user)
             + $this->getBonusEarnings($user);
    }

    /**
     * Get detailed earnings breakdown
     * 
     * @param User $user
     * @return array
     */
    public function getEarningsBreakdown(User $user): array
    {
        return [
            'commissions' => $this->getCommissionEarnings($user),
            'profit_shares' => $this->getProfitShareEarnings($user),
            'subscriptions' => $this->getSubscriptionEarnings($user),
            'product_sales' => $this->getProductSalesEarnings($user),
            'starter_kits' => $this->getStarterKitEarnings($user),
            'bonuses' => $this->getBonusEarnings($user),
            'lgr_daily_bonus' => $this->getLgrBalance($user),
            'total' => $this->calculateTotalEarnings($user),
        ];
    }

    /**
     * Get earnings for current month
     * 
     * @param User $user
     * @return float
     */
    public function getThisMonthEarnings(User $user): float
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return $this->getCommissionEarnings($user, $startOfMonth, $endOfMonth)
             + $this->getProfitShareEarnings($user, $startOfMonth, $endOfMonth)
             + $this->getBonusEarnings($user, $startOfMonth, $endOfMonth);
    }

    /**
     * Get earnings for a specific period
     * 
     * @param User $user
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getEarningsForPeriod(User $user, Carbon $startDate, Carbon $endDate): array
    {
        return [
            'commissions' => $this->getCommissionEarnings($user, $startDate, $endDate),
            'profit_shares' => $this->getProfitShareEarnings($user, $startDate, $endDate),
            'subscriptions' => $this->getSubscriptionEarnings($user, $startDate, $endDate),
            'product_sales' => $this->getProductSalesEarnings($user, $startDate, $endDate),
            'starter_kits' => $this->getStarterKitEarnings($user, $startDate, $endDate),
            'bonuses' => $this->getBonusEarnings($user, $startDate, $endDate),
            'total' => $this->getCommissionEarnings($user, $startDate, $endDate)
                     + $this->getProfitShareEarnings($user, $startDate, $endDate)
                     + $this->getBonusEarnings($user, $startDate, $endDate),
        ];
    }

    /**
     * Get commission earnings by level
     * 
     * @param User $user
     * @param int $level (1-7)
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getCommissionEarningsByLevel(
        User $user, 
        int $level, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = $user->referralCommissions()
            ->where('level', $level)
            ->where('status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get seven-level commission breakdown
     * 
     * @param User $user
     * @return array
     */
    public function getSevenLevelBreakdown(User $user): array
    {
        $breakdown = [];

        for ($level = 1; $level <= 7; $level++) {
            $breakdown[] = [
                'level' => $level,
                'total_earnings' => $this->getCommissionEarningsByLevel($user, $level),
                'this_month_earnings' => $this->getCommissionEarningsByLevel(
                    $user, 
                    $level, 
                    now()->startOfMonth(), 
                    now()->endOfMonth()
                ),
                'count' => $this->getReferralCountAtLevel($user, $level),
            ];
        }

        return $breakdown;
    }

    /**
     * Get pending (unpaid) earnings
     * 
     * @param User $user
     * @return float
     */
    public function getPendingEarnings(User $user): float
    {
        return (float) $user->referralCommissions()
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * Get monthly earnings history
     * 
     * @param User $user
     * @param int $months Number of months to retrieve
     * @return array
     */
    public function getMonthlyEarningsHistory(User $user, int $months = 6): array
    {
        $history = [];

        for ($i = 0; $i < $months; $i++) {
            $month = now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $history[] = [
                'month' => $month->format('M Y'),
                'earnings' => $this->getEarningsForPeriod($user, $startOfMonth, $endOfMonth),
            ];
        }

        return array_reverse($history);
    }

    /**
     * Get earnings statistics
     * 
     * @param User $user
     * @return array
     */
    public function getEarningsStatistics(User $user): array
    {
        $allTimeEarnings = $this->calculateTotalEarnings($user);
        $thisMonthEarnings = $this->getThisMonthEarnings($user);
        $lastMonthEarnings = $this->getEarningsForPeriod(
            $user,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        )['total'];

        $growthRate = $lastMonthEarnings > 0 
            ? (($thisMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100 
            : 0;

        return [
            'all_time' => $allTimeEarnings,
            'this_month' => $thisMonthEarnings,
            'last_month' => $lastMonthEarnings,
            'growth_rate' => round($growthRate, 2),
            'pending' => $this->getPendingEarnings($user),
            'average_monthly' => $this->getAverageMonthlyEarnings($user),
        ];
    }

    /**
     * Get average monthly earnings
     * 
     * @param User $user
     * @param int $months
     * @return float
     */
    public function getAverageMonthlyEarnings(User $user, int $months = 6): float
    {
        $history = $this->getMonthlyEarningsHistory($user, $months);
        $total = array_sum(array_column(array_column($history, 'earnings'), 'total'));

        return $total / $months;
    }

    // ==================== Private Helper Methods ====================

    /**
     * Get commission earnings
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getCommissionEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = $user->referralCommissions()->where('status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get profit share earnings
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getProfitShareEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = $user->profitShares();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get bonus earnings (achievement bonuses, etc.)
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getBonusEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        // Check if achievementBonuses relationship exists
        if (!method_exists($user, 'achievementBonuses')) {
            return 0.0;
        }

        $query = $user->achievementBonuses()->where('status', 'paid');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }
    
    /**
     * Get subscription earnings (commissions from team subscriptions)
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getSubscriptionEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->where('package_type', 'subscription');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }
    
    /**
     * Get product sales earnings (commissions from team product purchases)
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getProductSalesEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->where('package_type', 'product_purchase');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }
    
    /**
     * Get starter kit earnings (commissions from team starter kit purchases)
     * 
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    private function getStarterKitEarnings(
        User $user, 
        ?Carbon $startDate = null, 
        ?Carbon $endDate = null
    ): float {
        $query = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->where('package_type', 'starter_kit');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get referral count at specific level
     * 
     * @param User $user
     * @param int $level
     * @return int
     */
    private function getReferralCountAtLevel(User $user, int $level): int
    {
        return \App\Models\UserNetwork::where('referrer_id', $user->id)
            ->where('level', $level)
            ->count();
    }

    /**
     * Get LGR (Loyalty Growth Rewards) balance
     * 
     * @param User $user
     * @return float
     */
    private function getLgrBalance(User $user): float
    {
        return (float) ($user->loyalty_points ?? 0);
    }

    /**
     * Get LGR withdrawable amount (40% by default)
     * 
     * @param User $user
     * @return array
     */
    public function getLgrWithdrawableInfo(User $user): array
    {
        $lgrWithdrawablePercentage = $user->lgr_custom_withdrawable_percentage 
            ?? \App\Models\LgrSetting::get('lgr_max_cash_conversion', 40);
        
        $lgrAwardedTotal = (float) ($user->loyalty_points_awarded_total ?? 0);
        $lgrWithdrawnTotal = (float) ($user->loyalty_points_withdrawn_total ?? 0);
        $lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrWithdrawablePercentage / 100) - $lgrWithdrawnTotal;
        $lgrWithdrawable = min($user->loyalty_points, max(0, $lgrMaxWithdrawable));
        $lgrWithdrawalBlocked = (bool) ($user->lgr_withdrawal_blocked ?? false);
        
        if ($lgrWithdrawalBlocked) {
            $lgrWithdrawable = 0;
        }
        
        return [
            'balance' => (float) ($user->loyalty_points ?? 0),
            'withdrawable' => $lgrWithdrawable,
            'percentage' => $lgrWithdrawablePercentage,
            'blocked' => $lgrWithdrawalBlocked,
            'awarded_total' => $lgrAwardedTotal,
            'withdrawn_total' => $lgrWithdrawnTotal,
        ];
    }
}

