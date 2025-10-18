<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\ProfitDistribution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OptimizedInvestmentRepository
{
    /**
     * Get active investments by user with optimized query
     */
    public function getActiveInvestmentsByUserOptimized(User $user): Collection
    {
        $cacheKey = "active_investments_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return Investment::select([
                    'id', 'user_id', 'amount', 'tier', 'expected_return',
                    'total_earned', 'status', 'investment_date', 'lock_in_period_end',
                    'maturity_date', 'last_payout_date'
                ])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->orderBy('investment_date', 'desc')
                ->get();
        });
    }

    /**
     * Calculate total investment pool efficiently
     */
    public function getTotalInvestmentPoolOptimized(): float
    {
        return Cache::remember('total_investment_pool', 600, function () {
            return Investment::where('status', 'active')
                ->sum('amount');
        });
    }

    /**
     * Get investments by date range with pagination
     */
    public function getInvestmentsByDateRangePaginated(
        Carbon $startDate,
        Carbon $endDate,
        int $perPage = 50,
        array $statuses = ['active']
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        return Investment::select([
                'id', 'user_id', 'amount', 'tier', 'status',
                'investment_date', 'expected_return', 'total_earned'
            ])
            ->with(['user:id,name,email'])
            ->whereBetween('investment_date', [$startDate, $endDate])
            ->whereIn('status', $statuses)
            ->orderBy('investment_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Calculate user pool percentage efficiently
     */
    public function calculateUserPoolPercentageOptimized(User $user): float
    {
        $cacheKey = "user_pool_percentage_{$user->id}";
        
        return Cache::remember($cacheKey, 600, function () use ($user) {
            $userTotal = Investment::where('user_id', $user->id)
                ->where('status', 'active')
                ->sum('amount');

            $totalPool = $this->getTotalInvestmentPoolOptimized();

            return $totalPool > 0 ? ($userTotal / $totalPool) * 100 : 0;
        });
    }

    /**
     * Get investment metrics by tier with caching
     */
    public function getInvestmentMetricsByTierOptimized(): array
    {
        return Cache::remember('investment_metrics_by_tier', 900, function () {
            return Investment::select([
                    'tier',
                    DB::raw('COUNT(*) as total_investments'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('AVG(amount) as average_amount'),
                    DB::raw('SUM(total_earned) as total_earned'),
                    DB::raw('AVG(total_earned) as average_earned')
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
     * Get investments eligible for withdrawal
     */
    public function getWithdrawalEligibleInvestmentsOptimized(User $user): Collection
    {
        $cacheKey = "withdrawal_eligible_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return Investment::select([
                    'id', 'user_id', 'amount', 'tier', 'total_earned',
                    'investment_date', 'lock_in_period_end', 'status'
                ])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->where('lock_in_period_end', '<=', now())
                ->orderBy('investment_date')
                ->get();
        });
    }

    /**
     * Get investments approaching maturity
     */
    public function getInvestmentsApproachingMaturityOptimized(int $daysAhead = 30): Collection
    {
        $cacheKey = "approaching_maturity_{$daysAhead}";
        
        return Cache::remember($cacheKey, 1800, function () use ($daysAhead) {
            $targetDate = now()->addDays($daysAhead);
            
            return Investment::select([
                    'id', 'user_id', 'amount', 'tier', 'maturity_date',
                    'investment_date', 'total_earned'
                ])
                ->with(['user:id,name,email'])
                ->where('status', 'active')
                ->where('maturity_date', '<=', $targetDate)
                ->where('maturity_date', '>', now())
                ->orderBy('maturity_date')
                ->get();
        });
    }

    /**
     * Get investment performance metrics
     */
    public function getInvestmentPerformanceMetricsOptimized(
        User $user,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $cacheKey = "performance_metrics_{$user->id}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 600, function () use ($user, $startDate, $endDate) {
            $investments = Investment::select([
                    'amount', 'total_earned', 'investment_date', 'tier'
                ])
                ->where('user_id', $user->id)
                ->whereBetween('investment_date', [$startDate, $endDate])
                ->get();

            $totalInvested = $investments->sum('amount');
            $totalEarned = $investments->sum('total_earned');
            $roi = $totalInvested > 0 ? (($totalEarned / $totalInvested) * 100) : 0;

            return [
                'total_invested' => $totalInvested,
                'total_earned' => $totalEarned,
                'roi_percentage' => round($roi, 2),
                'investment_count' => $investments->count(),
                'tier_distribution' => $investments->groupBy('tier')->map->count()->toArray(),
                'average_investment' => $investments->count() > 0 ? $totalInvested / $investments->count() : 0
            ];
        });
    }

    /**
     * Get top investors by amount
     */
    public function getTopInvestorsOptimized(int $limit = 10): Collection
    {
        return Cache::remember("top_investors_{$limit}", 1800, function () use ($limit) {
            return DB::table('investments')
                ->select([
                    'user_id',
                    DB::raw('SUM(amount) as total_invested'),
                    DB::raw('SUM(total_earned) as total_earned'),
                    DB::raw('COUNT(*) as investment_count'),
                    DB::raw('MAX(investment_date) as latest_investment')
                ])
                ->join('users', 'investments.user_id', '=', 'users.id')
                ->addSelect(['users.name', 'users.email'])
                ->where('investments.status', 'active')
                ->groupBy('user_id', 'users.name', 'users.email')
                ->orderBy('total_invested', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get investment growth trends
     */
    public function getInvestmentGrowthTrendsOptimized(int $months = 12): array
    {
        $cacheKey = "investment_growth_trends_{$months}";
        
        return Cache::remember($cacheKey, 3600, function () use ($months) {
            $startDate = now()->subMonths($months)->startOfMonth();
            
            return Investment::select([
                    DB::raw('DATE_FORMAT(investment_date, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as investment_count'),
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('AVG(amount) as average_amount')
                ])
                ->where('investment_date', '>=', $startDate)
                ->where('status', 'active')
                ->groupBy(DB::raw('DATE_FORMAT(investment_date, "%Y-%m")'))
                ->orderBy('month')
                ->get()
                ->toArray();
        });
    }

    /**
     * Bulk update investment earnings efficiently
     */
    public function bulkUpdateInvestmentEarnings(array $updates): bool
    {
        if (empty($updates)) {
            return true;
        }

        try {
            DB::beginTransaction();

            // Use batch update for better performance
            $cases = [];
            $ids = [];
            
            foreach ($updates as $update) {
                $cases[] = "WHEN {$update['id']} THEN {$update['total_earned']}";
                $ids[] = $update['id'];
            }

            $casesString = implode(' ', $cases);
            $idsString = implode(',', $ids);

            DB::statement("
                UPDATE investments 
                SET total_earned = CASE id {$casesString} END,
                    last_payout_date = NOW(),
                    updated_at = NOW()
                WHERE id IN ({$idsString})
            ");

            // Clear related caches
            $this->clearInvestmentCaches($updates);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get profit distribution eligible investments
     */
    public function getProfitDistributionEligibleInvestmentsOptimized(
        Carbon $periodStart,
        Carbon $periodEnd
    ): Collection {
        $cacheKey = "profit_eligible_{$periodStart->format('Y-m-d')}_{$periodEnd->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 1800, function () use ($periodStart, $periodEnd) {
            return Investment::select([
                    'id', 'user_id', 'amount', 'tier', 'investment_date'
                ])
                ->with(['user:id,name,email,tier'])
                ->where('status', 'active')
                ->where('investment_date', '<=', $periodEnd)
                ->orderBy('investment_date')
                ->get();
        });
    }

    /**
     * Clear investment-related caches
     */
    private function clearInvestmentCaches(array $updates): void
    {
        $userIds = collect($updates)->pluck('user_id')->unique();
        
        foreach ($userIds as $userId) {
            Cache::forget("active_investments_{$userId}");
            Cache::forget("user_pool_percentage_{$userId}");
            Cache::forget("withdrawal_eligible_{$userId}");
        }

        // Clear global caches
        Cache::forget('total_investment_pool');
        Cache::forget('investment_metrics_by_tier');
        Cache::tags(['investment_trends'])->flush();
    }

    /**
     * Get investment statistics dashboard data
     */
    public function getInvestmentStatsDashboardOptimized(): array
    {
        return Cache::remember('investment_stats_dashboard', 600, function () {
            $totalActive = Investment::where('status', 'active')->count();
            $totalAmount = Investment::where('status', 'active')->sum('amount');
            $totalEarned = Investment::where('status', 'active')->sum('total_earned');
            $averageInvestment = $totalActive > 0 ? $totalAmount / $totalActive : 0;

            $monthlyGrowth = Investment::select([
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as amount')
                ])
                ->where('investment_date', '>=', now()->subMonth())
                ->where('status', 'active')
                ->first();

            return [
                'total_active_investments' => $totalActive,
                'total_investment_amount' => $totalAmount,
                'total_earnings_distributed' => $totalEarned,
                'average_investment_size' => round($averageInvestment, 2),
                'monthly_new_investments' => $monthlyGrowth->count ?? 0,
                'monthly_investment_amount' => $monthlyGrowth->amount ?? 0,
                'roi_average' => $totalAmount > 0 ? round(($totalEarned / $totalAmount) * 100, 2) : 0
            ];
        });
    }
}