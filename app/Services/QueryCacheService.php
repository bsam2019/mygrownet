<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueryCacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const SHORT_CACHE = 300;    // 5 minutes
    const MEDIUM_CACHE = 900;   // 15 minutes
    const LONG_CACHE = 3600;    // 1 hour
    const DAILY_CACHE = 86400;  // 24 hours

    /**
     * Get or cache dashboard metrics
     */
    public function getDashboardMetrics(): array
    {
        return Cache::remember('dashboard_metrics', self::MEDIUM_CACHE, function () {
            return [
                'total_users' => DB::table('users')->count(),
                'active_investments' => DB::table('investments')->where('status', 'active')->count(),
                'total_investment_amount' => DB::table('investments')->where('status', 'active')->sum('amount'),
                'total_commissions_paid' => DB::table('referral_commissions')->where('status', 'paid')->sum('amount'),
                'pending_withdrawals' => DB::table('withdrawal_requests')->where('status', 'pending')->count(),
                'monthly_new_users' => DB::table('users')->where('created_at', '>=', now()->subMonth())->count(),
                'monthly_investments' => DB::table('investments')->where('investment_date', '>=', now()->subMonth())->sum('amount')
            ];
        });
    }

    /**
     * Get or cache user statistics
     */
    public function getUserStats(int $userId): array
    {
        $cacheKey = "user_stats_{$userId}";
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($userId) {
            return [
                'total_investments' => DB::table('investments')->where('user_id', $userId)->where('status', 'active')->sum('amount'),
                'total_earnings' => DB::table('investments')->where('user_id', $userId)->sum('total_earned'),
                'referral_count' => DB::table('users')->where('referrer_id', $userId)->count(),
                'total_referral_earnings' => DB::table('referral_commissions')->where('referrer_id', $userId)->where('status', 'paid')->sum('amount'),
                'pending_withdrawals' => DB::table('withdrawal_requests')->where('user_id', $userId)->where('status', 'pending')->sum('amount'),
                'last_investment_date' => DB::table('investments')->where('user_id', $userId)->max('investment_date'),
                'investment_count' => DB::table('investments')->where('user_id', $userId)->where('status', 'active')->count()
            ];
        });
    }

    /**
     * Get or cache tier statistics
     */
    public function getTierStats(): array
    {
        return Cache::remember('tier_statistics', self::LONG_CACHE, function () {
            return DB::table('investments')
                ->select([
                    'tier',
                    DB::raw('COUNT(*) as investment_count'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('AVG(amount) as average_amount'),
                    DB::raw('SUM(total_earned) as total_earned')
                ])
                ->where('status', 'active')
                ->groupBy('tier')
                ->orderBy('total_amount', 'desc')
                ->get()
                ->keyBy('tier')
                ->toArray();
        });
    }

    /**
     * Get or cache matrix statistics
     */
    public function getMatrixStats(): array
    {
        return Cache::remember('matrix_statistics', self::MEDIUM_CACHE, function () {
            return [
                'total_positions' => DB::table('matrix_positions')->where('is_active', true)->count(),
                'filled_positions' => DB::table('matrix_positions')
                    ->where('is_active', true)
                    ->whereNotNull('left_child_id')
                    ->whereNotNull('middle_child_id')
                    ->whereNotNull('right_child_id')
                    ->count(),
                'average_level' => DB::table('matrix_positions')->where('is_active', true)->avg('level'),
                'max_level' => DB::table('matrix_positions')->where('is_active', true)->max('level'),
                'spillover_count' => DB::table('matrix_positions')->where('is_spillover', true)->count()
            ];
        });
    }

    /**
     * Get or cache commission statistics
     */
    public function getCommissionStats(): array
    {
        return Cache::remember('commission_statistics', self::MEDIUM_CACHE, function () {
            return [
                'total_commissions' => DB::table('referral_commissions')->where('status', 'paid')->sum('amount'),
                'pending_commissions' => DB::table('referral_commissions')->where('status', 'pending')->sum('amount'),
                'commission_count' => DB::table('referral_commissions')->where('status', 'paid')->count(),
                'average_commission' => DB::table('referral_commissions')->where('status', 'paid')->avg('amount'),
                'level_breakdown' => DB::table('referral_commissions')
                    ->select(['level', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total')])
                    ->where('status', 'paid')
                    ->groupBy('level')
                    ->orderBy('level')
                    ->get()
                    ->keyBy('level')
                    ->toArray()
            ];
        });
    }

    /**
     * Get or cache withdrawal statistics
     */
    public function getWithdrawalStats(): array
    {
        return Cache::remember('withdrawal_statistics', self::SHORT_CACHE, function () {
            return [
                'total_withdrawals' => DB::table('withdrawal_requests')->where('status', 'completed')->sum('amount'),
                'pending_withdrawals' => DB::table('withdrawal_requests')->where('status', 'pending')->sum('amount'),
                'withdrawal_count' => DB::table('withdrawal_requests')->where('status', 'completed')->count(),
                'average_withdrawal' => DB::table('withdrawal_requests')->where('status', 'completed')->avg('amount'),
                'total_penalties' => DB::table('withdrawal_requests')->where('status', 'completed')->sum('penalty_amount'),
                'monthly_withdrawals' => DB::table('withdrawal_requests')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->subMonth())
                    ->sum('amount')
            ];
        });
    }

    /**
     * Get or cache performance trends
     */
    public function getPerformanceTrends(int $months = 12): array
    {
        $cacheKey = "performance_trends_{$months}";
        
        return Cache::remember($cacheKey, self::LONG_CACHE, function () use ($months) {
            $startDate = now()->subMonths($months)->startOfMonth();
            
            return [
                'investment_trends' => DB::table('investments')
                    ->select([
                        DB::raw('DATE_FORMAT(investment_date, "%Y-%m") as month'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(amount) as total_amount')
                    ])
                    ->where('investment_date', '>=', $startDate)
                    ->groupBy(DB::raw('DATE_FORMAT(investment_date, "%Y-%m")'))
                    ->orderBy('month')
                    ->get()
                    ->toArray(),
                
                'commission_trends' => DB::table('referral_commissions')
                    ->select([
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(amount) as total_amount')
                    ])
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'paid')
                    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                    ->orderBy('month')
                    ->get()
                    ->toArray(),
                
                'withdrawal_trends' => DB::table('withdrawal_requests')
                    ->select([
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(amount) as total_amount')
                    ])
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'completed')
                    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                    ->orderBy('month')
                    ->get()
                    ->toArray()
            ];
        });
    }

    /**
     * Get or cache top performers
     */
    public function getTopPerformers(int $limit = 10): array
    {
        $cacheKey = "top_performers_{$limit}";
        
        return Cache::remember($cacheKey, self::MEDIUM_CACHE, function () use ($limit) {
            return [
                'top_investors' => DB::table('investments')
                    ->select([
                        'user_id',
                        DB::raw('SUM(amount) as total_invested'),
                        DB::raw('COUNT(*) as investment_count')
                    ])
                    ->join('users', 'investments.user_id', '=', 'users.id')
                    ->addSelect(['users.name', 'users.email'])
                    ->where('investments.status', 'active')
                    ->groupBy('user_id', 'users.name', 'users.email')
                    ->orderBy('total_invested', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray(),
                
                'top_referrers' => DB::table('users')
                    ->select(['id', 'name', 'email', 'referral_count', 'total_referral_earnings'])
                    ->where('referral_count', '>', 0)
                    ->orderBy('total_referral_earnings', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray()
            ];
        });
    }

    /**
     * Clear specific cache keys
     */
    public function clearCache(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear user-specific caches
     */
    public function clearUserCache(int $userId): void
    {
        $keys = [
            "user_stats_{$userId}",
            "active_investments_{$userId}",
            "user_pool_percentage_{$userId}",
            "withdrawal_eligible_{$userId}",
            "referral_tree_{$userId}_3",
            "matrix_structure_{$userId}",
            "commission_stats_{$userId}"
        ];
        
        $this->clearCache($keys);
    }

    /**
     * Clear all dashboard-related caches
     */
    public function clearDashboardCache(): void
    {
        $keys = [
            'dashboard_metrics',
            'tier_statistics',
            'matrix_statistics',
            'commission_statistics',
            'withdrawal_statistics',
            'investment_stats_dashboard'
        ];
        
        $this->clearCache($keys);
    }

    /**
     * Warm up frequently accessed caches
     */
    public function warmUpCaches(): void
    {
        // Warm up dashboard metrics
        $this->getDashboardMetrics();
        
        // Warm up tier stats
        $this->getTierStats();
        
        // Warm up matrix stats
        $this->getMatrixStats();
        
        // Warm up commission stats
        $this->getCommissionStats();
        
        // Warm up withdrawal stats
        $this->getWithdrawalStats();
        
        // Warm up performance trends
        $this->getPerformanceTrends();
        
        // Warm up top performers
        $this->getTopPerformers();
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $keys = [
            'dashboard_metrics',
            'tier_statistics', 
            'matrix_statistics',
            'commission_statistics',
            'withdrawal_statistics'
        ];
        
        $stats = [];
        foreach ($keys as $key) {
            $stats[$key] = [
                'exists' => Cache::has($key),
                'ttl' => Cache::has($key) ? 'cached' : 'not_cached'
            ];
        }
        
        return $stats;
    }
}