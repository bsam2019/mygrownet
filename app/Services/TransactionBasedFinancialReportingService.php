<?php

namespace App\Services;

use App\Models\User;
use App\Models\Module;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Transaction-Based Financial Reporting Service
 * 
 * Uses the transactions table as the single source of truth for all financial reporting.
 * Provides comprehensive financial metrics, compliance tracking, and module-based analysis.
 */
class TransactionBasedFinancialReportingService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get comprehensive financial overview
     */
    public function getFinancialOverview(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        // For custom period, use provided dates
        if ($period === 'custom' && $customStartDate && $customEndDate) {
            $startDate = Carbon::parse($customStartDate)->startOfDay();
            $endDate = Carbon::parse($customEndDate)->endOfDay();
            
            // Don't cache custom periods as they vary
            return [
                'revenue_metrics' => $this->getRevenueMetrics($startDate, $endDate),
                'expense_metrics' => $this->getExpenseMetrics($startDate, $endDate),
                'commission_metrics' => $this->getCommissionMetrics($startDate, $endDate),
                'profitability' => $this->getProfitabilityMetrics($startDate, $endDate),
                'cash_flow' => $this->getCashFlowMetrics($startDate, $endDate),
                'growth_metrics' => $this->getGrowthMetrics($startDate, $endDate),
                'module_performance' => $this->getModulePerformance($startDate, $endDate),
            ];
        }
        
        // For standard periods, use cache
        $startDate = $this->getPeriodStartDate($period);
        $endDate = now();
        
        return Cache::remember("financial_overview_{$period}", self::CACHE_TTL, function () use ($startDate, $endDate) {
            return [
                'revenue_metrics' => $this->getRevenueMetrics($startDate, $endDate),
                'expense_metrics' => $this->getExpenseMetrics($startDate, $endDate),
                'commission_metrics' => $this->getCommissionMetrics($startDate, $endDate),
                'profitability' => $this->getProfitabilityMetrics($startDate, $endDate),
                'cash_flow' => $this->getCashFlowMetrics($startDate, $endDate),
                'growth_metrics' => $this->getGrowthMetrics($startDate, $endDate),
                'module_performance' => $this->getModulePerformance($startDate, $endDate),
            ];
        });
    }

    /**
     * Get revenue metrics from transactions
     */
    private function getRevenueMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        $revenueTypes = [
            TransactionType::WALLET_TOPUP->value,
            TransactionType::SUBSCRIPTION_PAYMENT->value,
            TransactionType::PURCHASE->value,
            TransactionType::STARTER_KIT_PURCHASE->value,
            TransactionType::SERVICE_PAYMENT->value,
        ];

        // Use ABS() because wallet debits (purchases) are stored as negative amounts
        // but represent revenue (money spent by users on products/services)
        $revenue = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', $revenueTypes)
            ->select(
                DB::raw('SUM(ABS(amount)) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(ABS(amount)) as average')
            )
            ->first();

        // Get previous period for growth calculation
        $periodDays = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($periodDays);
        $previousRevenue = DB::table('transactions')
            ->whereBetween('created_at', [$previousStart, $startDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', $revenueTypes)
            ->sum(DB::raw('ABS(amount)'));

        $growthRate = $previousRevenue > 0 
            ? (($revenue->total - $previousRevenue) / $previousRevenue) * 100 
            : 0;

        return [
            'total_revenue' => (float) $revenue->total,
            'transaction_count' => (int) $revenue->count,
            'average_transaction' => (float) $revenue->average,
            'growth_rate' => round($growthRate, 2),
            'previous_period' => (float) $previousRevenue,
        ];
    }

    /**
     * Get expense metrics from transactions
     */
    private function getExpenseMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        $expenseTypes = [
            TransactionType::WITHDRAWAL->value,
            TransactionType::COMMISSION_PAYOUT->value,
            TransactionType::PROFIT_SHARE_PAYOUT->value,
            TransactionType::LOAN_REPAYMENT->value,
        ];

        $expenses = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', $expenseTypes)
            ->select(
                DB::raw('SUM(ABS(amount)) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->first();

        return [
            'total_expenses' => (float) $expenses->total,
            'transaction_count' => (int) $expenses->count,
        ];
    }

    /**
     * Get commission metrics
     */
    private function getCommissionMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        // Commissions earned (from referral_commissions table)
        $commissionsEarned = DB::table('referral_commissions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as pending')
            )
            ->first();

        // Commission payouts (from transactions table)
        $commissionPayouts = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::COMMISSION_PAYOUT->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        return [
            'total_earned' => (float) $commissionsEarned->total,
            'paid_out' => abs((float) $commissionPayouts),
            'pending' => (float) $commissionsEarned->pending,
            'payout_rate' => $commissionsEarned->total > 0 
                ? (abs($commissionPayouts) / $commissionsEarned->total) * 100 
                : 0,
        ];
    }

    /**
     * Get profitability metrics
     */
    private function getProfitabilityMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        $revenue = $this->getRevenueMetrics($startDate, $endDate)['total_revenue'];
        $expenses = $this->getExpenseMetrics($startDate, $endDate)['total_expenses'];
        $profit = $revenue - $expenses;

        return [
            'gross_profit' => $profit,
            'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            'revenue' => $revenue,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get cash flow metrics
     */
    private function getCashFlowMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        // Inflows (credits)
        $inflows = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('amount', '>', 0)
            ->sum('amount');

        // Outflows (debits)
        $outflows = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('amount', '<', 0)
            ->sum('amount');

        $netCashFlow = $inflows + $outflows; // outflows are negative

        return [
            'inflows' => (float) $inflows,
            'outflows' => abs((float) $outflows),
            'net_cash_flow' => (float) $netCashFlow,
            'cash_flow_ratio' => $outflows != 0 ? $inflows / abs($outflows) : 0,
        ];
    }

    /**
     * Get growth metrics
     */
    private function getGrowthMetrics(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        $currentPeriod = $this->getRevenueMetrics($startDate, $endDate);
        
        // User growth
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $periodDays = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($periodDays);
        $previousUsers = User::whereBetween('created_at', [$previousStart, $startDate])->count();
        
        $userGrowthRate = $previousUsers > 0 
            ? (($newUsers - $previousUsers) / $previousUsers) * 100 
            : 0;

        return [
            'revenue_growth_rate' => $currentPeriod['growth_rate'],
            'user_growth_rate' => round($userGrowthRate, 2),
            'new_users' => $newUsers,
            'transaction_growth' => $this->getTransactionGrowthRate($startDate, $endDate),
        ];
    }

    /**
     * Get module performance metrics
     */
    private function getModulePerformance(Carbon $startDate, ?Carbon $endDate = null): array
    {
        $endDate = $endDate ?? now();
        
        $modules = Module::all();
        $performance = [];

        foreach ($modules as $module) {
            $revenue = DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', TransactionStatus::COMPLETED->value)
                ->where('module_id', $module->id)
                ->where('amount', '>', 0)
                ->sum('amount');

            $transactionCount = DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('module_id', $module->id)
                ->count();

            $performance[] = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'revenue' => (float) $revenue,
                'transaction_count' => $transactionCount,
                'average_transaction' => $transactionCount > 0 ? $revenue / $transactionCount : 0,
            ];
        }

        // Sort by revenue descending
        usort($performance, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        return $performance;
    }

    /**
     * Get transaction breakdown by type
     */
    public function getTransactionBreakdown(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $breakdown = DB::table('transactions')
            ->where('created_at', '>=', $startDate)
            ->select(
                'transaction_type',
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'),
                DB::raw('AVG(amount) as average')
            )
            ->groupBy('transaction_type', 'status')
            ->get();

        $result = [];
        foreach ($breakdown as $item) {
            if (!isset($result[$item->transaction_type])) {
                $result[$item->transaction_type] = [
                    'type' => $item->transaction_type,
                    'total_count' => 0,
                    'total_amount' => 0,
                    'by_status' => [],
                ];
            }

            $result[$item->transaction_type]['total_count'] += $item->count;
            $result[$item->transaction_type]['total_amount'] += $item->total;
            $result[$item->transaction_type]['by_status'][$item->status] = [
                'count' => (int) $item->count,
                'total' => (float) $item->total,
                'average' => (float) $item->average,
            ];
        }

        return array_values($result);
    }

    /**
     * Get revenue by module
     */
    public function getRevenueByModule(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $moduleRevenue = DB::table('transactions')
            ->join('financial_modules', 'transactions.module_id', '=', 'financial_modules.id')
            ->where('transactions.created_at', '>=', $startDate)
            ->where('transactions.status', TransactionStatus::COMPLETED->value)
            ->where('transactions.amount', '>', 0)
            ->select(
                'financial_modules.id',
                'financial_modules.name',
                'financial_modules.code',
                DB::raw('SUM(transactions.amount) as revenue'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->groupBy('financial_modules.id', 'financial_modules.name', 'financial_modules.code')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $moduleRevenue->sum('revenue');

        return $moduleRevenue->map(function ($item) use ($totalRevenue) {
            return [
                'module_id' => $item->id,
                'module_name' => $item->name,
                'module_code' => $item->code,
                'revenue' => (float) $item->revenue,
                'transaction_count' => (int) $item->transaction_count,
                'percentage' => $totalRevenue > 0 ? ($item->revenue / $totalRevenue) * 100 : 0,
            ];
        })->toArray();
    }

    /**
     * Get transaction trends (daily breakdown)
     */
    public function getTransactionTrends(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $trends = DB::table('transactions')
            ->where('created_at', '>=', $startDate)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as expenses'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $trends->map(function ($item) {
            return [
                'date' => $item->date,
                'revenue' => (float) $item->revenue,
                'expenses' => (float) $item->expenses,
                'net' => (float) ($item->revenue - $item->expenses),
                'transaction_count' => (int) $item->transaction_count,
            ];
        })->toArray();
    }

    /**
     * Get user financial summary
     */
    public function getUserFinancialSummary(int $userId, string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $transactions = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_credits'),
                DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_debits'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->first();

        $byType = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->select(
                'transaction_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('transaction_type')
            ->get();

        return [
            'user_id' => $userId,
            'period' => $period,
            'total_credits' => (float) $transactions->total_credits,
            'total_debits' => (float) $transactions->total_debits,
            'net_balance' => (float) ($transactions->total_credits - $transactions->total_debits),
            'transaction_count' => (int) $transactions->transaction_count,
            'by_type' => $byType->map(fn($item) => [
                'type' => $item->transaction_type,
                'count' => (int) $item->count,
                'total' => (float) $item->total,
            ])->toArray(),
        ];
    }

    /**
     * Get compliance metrics
     */
    public function getComplianceMetrics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        // Commission to revenue ratio
        $revenue = $this->getRevenueMetrics($startDate)['total_revenue'];
        $commissions = $this->getCommissionMetrics($startDate)['total_earned'];
        $commissionRatio = $revenue > 0 ? ($commissions / $revenue) * 100 : 0;

        // Payout timing compliance (commissions paid within 24 hours)
        $totalCommissions = DB::table('referral_commissions')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'paid')
            ->count();

        $timelyPayouts = DB::table('referral_commissions')
            ->where('created_at', '>=', $startDate)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, paid_at) <= 24')
            ->count();

        $payoutCompliance = $totalCommissions > 0 
            ? ($timelyPayouts / $totalCommissions) * 100 
            : 100;

        return [
            'commission_to_revenue_ratio' => round($commissionRatio, 2),
            'commission_cap_threshold' => 25.0,
            'commission_cap_compliant' => $commissionRatio <= 25.0,
            'payout_timing_compliance' => round($payoutCompliance, 2),
            'timely_payouts' => $timelyPayouts,
            'total_payouts' => $totalCommissions,
        ];
    }

    /**
     * Get top revenue sources
     */
    public function getTopRevenueSources(string $period = 'month', int $limit = 10): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $sources = DB::table('transactions')
            ->where('created_at', '>=', $startDate)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('amount', '>', 0)
            ->select(
                'source',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $sources->map(fn($item) => [
            'source' => $item->source,
            'transaction_count' => (int) $item->count,
            'total_revenue' => (float) $item->total,
        ])->toArray();
    }

    /**
     * Get withdrawal statistics
     */
    public function getWithdrawalStatistics(string $period = 'month'): array
    {
        $startDate = $this->getPeriodStartDate($period);

        $withdrawals = DB::table('transactions')
            ->where('created_at', '>=', $startDate)
            ->where('transaction_type', TransactionType::WITHDRAWAL->value)
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(ABS(amount)) as total'),
                DB::raw('AVG(ABS(amount)) as average')
            )
            ->groupBy('status')
            ->get();

        $result = [
            'total_count' => 0,
            'total_amount' => 0,
            'average_amount' => 0,
            'by_status' => [],
        ];

        foreach ($withdrawals as $item) {
            $result['total_count'] += $item->count;
            $result['total_amount'] += $item->total;
            $result['by_status'][$item->status] = [
                'count' => (int) $item->count,
                'total' => (float) $item->total,
                'average' => (float) $item->average,
            ];
        }

        $result['average_amount'] = $result['total_count'] > 0 
            ? $result['total_amount'] / $result['total_count'] 
            : 0;

        return $result;
    }

    /**
     * Helper: Get period start date
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };
    }

    /**
     * Helper: Get previous period start date
     */
    private function getPreviousPeriodStart(Carbon $currentStart): Carbon
    {
        $diff = now()->diffInDays($currentStart);
        return $currentStart->copy()->subDays($diff);
    }

    /**
     * Helper: Get transaction growth rate
     */
    private function getTransactionGrowthRate(Carbon $startDate, ?Carbon $endDate = null): float
    {
        $endDate = $endDate ?? now();
        
        $currentCount = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $periodDays = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($periodDays);
        $previousCount = DB::table('transactions')
            ->whereBetween('created_at', [$previousStart, $startDate])
            ->count();

        return $previousCount > 0 
            ? (($currentCount - $previousCount) / $previousCount) * 100 
            : 0;
    }

    /**
     * Clear all caches
     */
    public function clearCache(): void
    {
        $periods = ['today', 'week', 'month', 'quarter', 'year'];
        foreach ($periods as $period) {
            Cache::forget("financial_overview_{$period}");
        }
    }
}
