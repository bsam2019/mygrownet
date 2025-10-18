<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PerformanceMonitoringService;
use App\Services\QueryCacheService;
use Carbon\Carbon;

class DashboardService
{
    private PerformanceMonitoringService $performanceMonitoring;
    private QueryCacheService $cacheService;

    public function __construct(
        PerformanceMonitoringService $performanceMonitoring,
        QueryCacheService $cacheService
    ) {
        $this->performanceMonitoring = $performanceMonitoring;
        $this->cacheService = $cacheService;
    }
    /**
     * Alias for getUserStats method
     */
    public function getUserStatistics(User $user)
    {
        return $this->getUserStats($user);
    }

    public function getUserStats(User $user)
    {
        return $this->performanceMonitoring->monitorQuery(
            'dashboard_user_stats',
            function () use ($user) {
                // Use cached user stats with fallback to database
                $cachedStats = $this->cacheService->getUserStats($user->id);
                
                if (!empty($cachedStats)) {
                    return $cachedStats;
                }

                $now = Carbon::now();
                $monthStart = $now->startOfMonth();

                return [
                    'investments' => [
                        'total' => $user->investments()->where('status', 'active')->sum('amount'),
                        'count' => $user->investments()->where('status', 'active')->count(),
                        'monthlyReturn' => $this->calculateMonthlyReturn($user),
                        'nextPayout' => $this->calculateNextPayout($user)
                    ],
                    'referrals' => [
                        'total' => $user->directReferrals()->count(),
                        'active' => $user->directReferrals()
                            ->whereHas('investments', function($query) {
                                $query->where('status', 'active');
                            })->count(),
                        'monthlyCommission' => $user->referralCommissions()
                            ->where('created_at', '>=', $monthStart)
                            ->sum('amount')
                    ],
                    'transactions' => [
                        'pending' => $user->transactions()
                            ->where('status', 'pending')
                            ->count(),
                        'monthlyWithdrawals' => $user->transactions()
                            ->where('transaction_type', 'withdrawal')
                            ->where('created_at', '>=', $monthStart)
                            ->sum('amount')
                    ]
                ];
            },
            ['user_id' => $user->id, 'action' => 'user_stats']
        );
    }

    /**
     * Get recent investments for the dashboard
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentInvestments()
    {
        return $this->performanceMonitoring->monitorQuery(
            'dashboard_recent_investments',
            function () {
                return Investment::select(['id', 'user_id', 'amount', 'tier', 'status', 'created_at'])
                    ->with(['user:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            },
            ['action' => 'recent_investments']
        );
    }

    /**
     * Get recent transactions for the dashboard
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentTransactions()
    {
        return $this->performanceMonitoring->monitorQuery(
            'dashboard_recent_transactions',
            function () {
                return Transaction::select(['id', 'user_id', 'amount', 'transaction_type', 'status', 'created_at'])
                    ->with(['user:id,name,email'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            },
            ['action' => 'recent_transactions']
        );
    }

    private function calculateMonthlyReturn(User $user)
    {
        $monthStart = Carbon::now()->startOfMonth();

        return $user->transactions()
            ->where('transaction_type', 'return')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');
    }

    private function calculateNextPayout(User $user)
    {
        $activeInvestments = $user->investments()
            ->where('status', 'active')
            ->get();

        $totalPayout = 0;
        foreach ($activeInvestments as $investment) {
            $rate = $this->getReturnRate($investment->amount);
            $totalPayout += ($investment->amount * ($rate / 100));
        }

        return $totalPayout;
    }

    private function getReturnRate($amount)
    {
        // Return rates based on investment tiers (ZMW)
        return match(true) {
            $amount >= 50000 => 45, // Elite tier
            $amount >= 25000 => 35, // Leader tier
            $amount >= 10000 => 25, // Builder tier
            $amount >= 5000 => 20,  // Starter tier
            default => 15           // Basic tier
        };
    }
}
