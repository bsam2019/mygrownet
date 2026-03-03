<?php

namespace App\Services;

use App\Models\Module;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Profit & Loss Tracking Service
 * 
 * Tracks all revenue and expenses to calculate platform profitability.
 * Uses transactions table as single source of truth.
 */
class ProfitLossTrackingService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get comprehensive P&L statement
     */
    public function getProfitLossStatement(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $startDate = $customStartDate ? \Carbon\Carbon::parse($customStartDate) : $this->getPeriodStartDate($period);
        $endDate = $customEndDate ? \Carbon\Carbon::parse($customEndDate) : now();

        return [
            'period' => $period,
            'date_range' => [
                'from' => $startDate->toDateString(),
                'to' => $endDate->toDateString(),
            ],
            'revenue' => $this->getRevenue($startDate, $endDate),
            'expenses' => $this->getExpenses($startDate, $endDate),
            'profitability' => $this->calculateProfitability($startDate, $endDate),
            'by_module' => $this->getProfitLossByModule($startDate, $endDate),
            'trends' => $this->getProfitLossTrends($startDate, $endDate),
        ];
    }

    /**
     * Get all revenue sources
     */
    private function getRevenue(Carbon $startDate, Carbon $endDate): array
    {
        // Revenue transaction types
        $revenueTypes = [
            TransactionType::WALLET_TOPUP->value,
            TransactionType::SUBSCRIPTION_PAYMENT->value,
            TransactionType::STARTER_KIT_PURCHASE->value,
            TransactionType::STARTER_KIT_UPGRADE->value,
            TransactionType::SHOP_PURCHASE->value,
            TransactionType::SERVICE_PAYMENT->value,
            TransactionType::WORKSHOP_PAYMENT->value,
            TransactionType::LEARNING_PACK_PURCHASE->value,
            TransactionType::COACHING_PAYMENT->value,
            TransactionType::GROWBUILDER_PAYMENT->value,
            TransactionType::MARKETPLACE_PURCHASE->value,
        ];

        $breakdown = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', $revenueTypes)
            ->select(
                'transaction_type',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('transaction_type')
            ->get();

        $revenueBreakdown = [];
        $totalRevenue = 0;

        foreach ($breakdown as $item) {
            $amount = (float) $item->total;
            $revenueBreakdown[$item->transaction_type] = [
                'amount' => $amount,
                'count' => (int) $item->count,
                'label' => $this->formatTransactionType($item->transaction_type),
            ];
            $totalRevenue += $amount;
        }
        
        // Calculate percentages
        foreach ($revenueBreakdown as $key => $item) {
            $revenueBreakdown[$key]['percentage'] = $totalRevenue > 0 
                ? ($item['amount'] / $totalRevenue) * 100 
                : 0;
        }

        return [
            'total' => $totalRevenue,
            'breakdown' => $revenueBreakdown,
        ];
    }

    /**
     * Get all expenses
     */
    private function getExpenses(Carbon $startDate, Carbon $endDate): array
    {
        // Commission expenses
        $commissions = DB::table('referral_commissions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('amount');

        // Withdrawal expenses
        $withdrawals = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::WITHDRAWAL->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum(DB::raw('ABS(amount)'));

        // Profit share expenses
        $profitShares = DB::table('profit_shares')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('amount');

        // LGR expenses
        $lgrExpenses = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('transaction_type', [
                TransactionType::LGR_EARNED->value,
                TransactionType::LGR_MANUAL_AWARD->value,
            ])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        // Loan disbursements (money given out)
        $loanDisbursements = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::LOAN_DISBURSEMENT->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        // Shop credit allocations (free money given)
        $shopCredits = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::SHOP_CREDIT_ALLOCATION->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        // CMS Platform Expenses (from CMS expense management)
        $cmsExpenses = $this->getCmsExpenses($startDate, $endDate);

        $totalExpenses = $commissions + $withdrawals + $profitShares + $lgrExpenses + $loanDisbursements + $shopCredits + $cmsExpenses['total'];

        $expenseBreakdown = [
            'commissions' => [
                'amount' => (float) $commissions,
                'count' => 0, // Aggregated count not available for these
                'label' => 'Referral Commissions',
            ],
            'withdrawals' => [
                'amount' => (float) $withdrawals,
                'count' => 0,
                'label' => 'Member Withdrawals',
            ],
            'profit_shares' => [
                'amount' => (float) $profitShares,
                'count' => 0,
                'label' => 'Community Profit Sharing',
            ],
            'lgr_rewards' => [
                'amount' => (float) $lgrExpenses,
                'count' => 0,
                'label' => 'Loyalty Growth Rewards',
            ],
            'loan_disbursements' => [
                'amount' => (float) $loanDisbursements,
                'count' => 0,
                'label' => 'Loan Disbursements',
            ],
            'shop_credits' => [
                'amount' => (float) $shopCredits,
                'count' => 0,
                'label' => 'Shop Credit Allocations',
            ],
            'platform_expenses' => [
                'amount' => (float) $cmsExpenses['total'],
                'count' => 0,
                'label' => 'Platform Expenses',
                'breakdown' => $cmsExpenses['breakdown'],
                'source' => 'cms',
            ],
        ];
        
        // Calculate percentages
        foreach ($expenseBreakdown as $key => $item) {
            $expenseBreakdown[$key]['percentage'] = $totalExpenses > 0 
                ? ($item['amount'] / $totalExpenses) * 100 
                : 0;
        }

        return [
            'total' => $totalExpenses,
            'breakdown' => $expenseBreakdown,
        ];
    }

    /**
     * Get CMS platform expenses
     */
    private function getCmsExpenses(Carbon $startDate, Carbon $endDate): array
    {
        $expenseTypes = [
            TransactionType::MARKETING_EXPENSE->value,
            TransactionType::OFFICE_EXPENSE->value,
            TransactionType::TRAVEL_EXPENSE->value,
            TransactionType::INFRASTRUCTURE_EXPENSE->value,
            TransactionType::LEGAL_EXPENSE->value,
            TransactionType::PROFESSIONAL_FEES->value,
            TransactionType::UTILITIES_EXPENSE->value,
            TransactionType::GENERAL_EXPENSE->value,
        ];

        $breakdown = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', $expenseTypes)
            ->select(
                'transaction_type',
                DB::raw('SUM(ABS(amount)) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('transaction_type')
            ->get();

        $expenseBreakdown = [];
        $totalExpenses = 0;

        foreach ($breakdown as $item) {
            $amount = (float) $item->total;
            $type = TransactionType::from($item->transaction_type);
            
            $expenseBreakdown[$item->transaction_type] = [
                'amount' => $amount,
                'count' => (int) $item->count,
                'label' => $type->label(),
            ];
            $totalExpenses += $amount;
        }
        
        // Calculate percentages
        foreach ($expenseBreakdown as $key => $item) {
            $expenseBreakdown[$key]['percentage'] = $totalExpenses > 0 
                ? ($item['amount'] / $totalExpenses) * 100 
                : 0;
        }

        return [
            'total' => $totalExpenses,
            'breakdown' => $expenseBreakdown,
        ];
    }

    /**
     * Calculate profitability metrics
     */
    private function calculateProfitability(Carbon $startDate, Carbon $endDate): array
    {
        $revenue = $this->getRevenue($startDate, $endDate);
        $expenses = $this->getExpenses($startDate, $endDate);

        $grossProfit = $revenue['total'] - $expenses['total'];
        $profitMargin = $revenue['total'] > 0 ? ($grossProfit / $revenue['total']) * 100 : 0;

        // Calculate expense ratios
        $expenseRatios = [];
        foreach ($expenses['breakdown'] as $key => $expense) {
            $expenseRatios[$key] = $revenue['total'] > 0 
                ? ($expense['amount'] / $revenue['total']) * 100 
                : 0;
        }

        return [
            'gross_profit' => $grossProfit,
            'profit_margin' => round($profitMargin, 2),
            'total_revenue' => $revenue['total'],
            'total_expenses' => $expenses['total'],
            'expense_ratios' => $expenseRatios,
            'is_profitable' => $grossProfit > 0,
        ];
    }

    /**
     * Get P&L by module
     */
    private function getProfitLossByModule(Carbon $startDate, Carbon $endDate): array
    {
        $modules = Module::where('is_active', true)->get();
        $modulePL = [];

        foreach ($modules as $module) {
            // Revenue from this module (using transaction_source)
            $revenue = DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', TransactionStatus::COMPLETED->value)
                ->where('transaction_source', $module->code)
                ->where('amount', '>', 0)
                ->sum('amount');

            // Expenses attributed to this module
            // For now, we'll calculate expenses from transactions with negative amounts from this module
            $expenses = DB::table('transactions')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', TransactionStatus::COMPLETED->value)
                ->where('transaction_source', $module->code)
                ->where('amount', '<', 0)
                ->sum('amount');
            
            $expenses = abs($expenses); // Make positive for display

            $profit = $revenue - $expenses;
            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

            // Only include modules with activity
            if ($revenue > 0 || $expenses > 0) {
                $modulePL[] = [
                    'module_id' => $module->id,
                    'module_name' => $module->name,
                    'module_code' => $module->code,
                    'revenue' => (float) $revenue,
                    'expenses' => (float) $expenses,
                    'profit' => $profit,
                    'profit_margin' => round($margin, 2),
                ];
            }
        }

        // Sort by profit descending
        usort($modulePL, fn($a, $b) => $b['profit'] <=> $a['profit']);

        return $modulePL;
    }

    /**
     * Get P&L trends (daily breakdown)
     */
    private function getProfitLossTrends(Carbon $startDate, Carbon $endDate): array
    {
        $trends = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            $revenue = $this->getRevenue($dayStart, $dayEnd);
            $expenses = $this->getExpenses($dayStart, $dayEnd);
            $profit = $revenue['total'] - $expenses['total'];

            $trends[] = [
                'date' => $currentDate->toDateString(),
                'revenue' => $revenue['total'],
                'expenses' => $expenses['total'],
                'profit' => $profit,
            ];

            $currentDate->addDay();
        }

        return $trends;
    }

    /**
     * Get commission efficiency metrics
     */
    public function getCommissionEfficiency(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $startDate = $customStartDate ? \Carbon\Carbon::parse($customStartDate) : $this->getPeriodStartDate($period);
        $endDate = $customEndDate ? \Carbon\Carbon::parse($customEndDate) : now();

        $totalRevenue = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalCommissions = DB::table('referral_commissions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('amount');

        $commissionRatio = $totalRevenue > 0 ? ($totalCommissions / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => (float) $totalRevenue,
            'total_commissions' => (float) $totalCommissions,
            'commission_ratio' => round($commissionRatio, 2),
            'commission_cap' => 25.0,
            'is_compliant' => $commissionRatio <= 25.0,
            'available_margin' => max(0, 25.0 - $commissionRatio),
        ];
    }

    /**
     * Get cash flow analysis
     */
    public function getCashFlowAnalysis(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $startDate = $customStartDate ? \Carbon\Carbon::parse($customStartDate) : $this->getPeriodStartDate($period);
        $endDate = $customEndDate ? \Carbon\Carbon::parse($customEndDate) : now();

        // Cash inflows (actual money coming in)
        $deposits = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::WALLET_TOPUP->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        // Cash outflows (actual money going out)
        $withdrawals = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('transaction_type', TransactionType::WITHDRAWAL->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum(DB::raw('ABS(amount)'));

        $loanDisbursements = DB::table('transactions')
            ->where('created_at', '>=', $startDate)
            ->where('transaction_type', TransactionType::LOAN_DISBURSEMENT->value)
            ->where('status', TransactionStatus::COMPLETED->value)
            ->sum('amount');

        $netCashFlow = $deposits - ($withdrawals + $loanDisbursements);

        return [
            'cash_inflows' => (float) $deposits,
            'cash_outflows' => (float) ($withdrawals + $loanDisbursements),
            'net_cash_flow' => $netCashFlow,
            'cash_flow_ratio' => $withdrawals > 0 ? $deposits / $withdrawals : 0,
        ];
    }

    /**
     * Get period start date
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            'all' => Carbon::parse('2020-01-01'), // Platform start
            default => now()->startOfMonth()
        };
    }

    /**
     * Format transaction type for display
     */
    private function formatTransactionType(string $type): string
    {
        return str_replace('_', ' ', ucwords($type, '_'));
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        // Cache clearing if needed
    }
}
