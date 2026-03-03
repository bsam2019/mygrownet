<?php

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel;
use App\Infrastructure\Persistence\Eloquent\CMS\BudgetItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetComparisonService
{
    private const PLATFORM_COMPANY_NAME = 'MyGrowNet Platform';
    
    /**
     * Get MyGrowNet Platform company ID
     */
    private function getPlatformCompanyId(): ?int
    {
        return CompanyModel::where('name', self::PLATFORM_COMPANY_NAME)->value('id');
    }
    
    /**
     * Get active budget for the specified period
     */
    public function getActiveBudget(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): ?array
    {
        $companyId = $this->getPlatformCompanyId();
        if (!$companyId) {
            return null;
        }
        
        $dates = $this->getPeriodDates($period, $customStartDate, $customEndDate);
        
        $budget = BudgetModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->forPeriod($dates['start'], $dates['end'])
            ->with('items')
            ->first();
            
        if (!$budget) {
            return null;
        }
        
        return [
            'id' => $budget->id,
            'name' => $budget->name,
            'period_type' => $budget->period_type,
            'start_date' => $budget->start_date->format('Y-m-d'),
            'end_date' => $budget->end_date->format('Y-m-d'),
            'total_budget' => (float) $budget->total_budget,
            'status' => $budget->status,
            'items' => $budget->items->map(fn($item) => [
                'id' => $item->id,
                'category' => $item->category,
                'item_type' => $item->item_type,
                'budgeted_amount' => (float) $item->budgeted_amount,
                'notes' => $item->notes,
            ])->toArray(),
        ];
    }
    
    /**
     * Get actual expenses from transactions for the period
     */
    public function getActualExpenses(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $dates = $this->getPeriodDates($period, $customStartDate, $customEndDate);
        
        $expenses = Transaction::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('transaction_type', $this->getExpenseTypes())
            ->where('status', 'completed')
            ->select('transaction_type', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type')
            ->get();
            
        $result = [];
        foreach ($expenses as $expense) {
            $category = $this->mapTransactionTypeToCategory($expense->transaction_type);
            if (!isset($result[$category])) {
                $result[$category] = 0;
            }
            $result[$category] += abs($expense->total);
        }
        
        return $result;
    }
    
    /**
     * Get actual revenue from transactions for the period
     */
    public function getActualRevenue(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $dates = $this->getPeriodDates($period, $customStartDate, $customEndDate);
        
        $revenue = Transaction::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->whereIn('transaction_type', $this->getRevenueTypes())
            ->where('status', 'completed')
            ->select('transaction_type', DB::raw('SUM(amount) as total'))
            ->groupBy('transaction_type')
            ->get();
            
        $result = [];
        foreach ($revenue as $rev) {
            $category = $this->mapTransactionTypeToCategory($rev->transaction_type);
            if (!isset($result[$category])) {
                $result[$category] = 0;
            }
            $result[$category] += abs($rev->total);
        }
        
        return $result;
    }
    
    /**
     * Compare budget vs actual for the period
     */
    public function compareActualVsBudget(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $budget = $this->getActiveBudget($period, $customStartDate, $customEndDate);
        
        if (!$budget) {
            return [
                'has_budget' => false,
                'message' => 'No active budget found for this period',
                'period' => $period,
                'dates' => $this->getPeriodDates($period, $customStartDate, $customEndDate),
            ];
        }
        
        $actualExpenses = $this->getActualExpenses($period, $customStartDate, $customEndDate);
        $actualRevenue = $this->getActualRevenue($period, $customStartDate, $customEndDate);
        
        // Process budget items
        $comparisons = [];
        $totalBudgeted = 0;
        $totalActual = 0;
        
        foreach ($budget['items'] as $item) {
            $category = $item['category'];
            $budgetedAmount = $item['budgeted_amount'];
            $itemType = $item['item_type'];
            
            // Get actual amount based on item type
            $actualAmount = 0;
            if ($itemType === 'expense') {
                $actualAmount = $actualExpenses[$category] ?? 0;
            } elseif ($itemType === 'revenue') {
                $actualAmount = $actualRevenue[$category] ?? 0;
            }
            
            $variance = $actualAmount - $budgetedAmount;
            $percentageUsed = $budgetedAmount > 0 ? ($actualAmount / $budgetedAmount) * 100 : 0;
            
            $comparisons[] = [
                'category' => $category,
                'item_type' => $itemType,
                'budgeted' => $budgetedAmount,
                'actual' => $actualAmount,
                'variance' => $variance,
                'percentage_used' => round($percentageUsed, 2),
                'status' => $this->getVarianceStatus($variance, $itemType, $percentageUsed),
                'notes' => $item['notes'],
            ];
            
            $totalBudgeted += $budgetedAmount;
            $totalActual += $actualAmount;
        }
        
        // Check for actual expenses not in budget
        $unbudgetedExpenses = [];
        foreach ($actualExpenses as $category => $amount) {
            $inBudget = collect($budget['items'])->contains('category', $category);
            if (!$inBudget && $amount > 0) {
                $unbudgetedExpenses[] = [
                    'category' => $category,
                    'amount' => $amount,
                ];
            }
        }
        
        return [
            'has_budget' => true,
            'budget' => $budget,
            'period' => $period,
            'dates' => $this->getPeriodDates($period, $customStartDate, $customEndDate),
            'comparisons' => $comparisons,
            'unbudgeted_expenses' => $unbudgetedExpenses,
            'summary' => [
                'total_budgeted' => $totalBudgeted,
                'total_actual' => $totalActual,
                'total_variance' => $totalActual - $totalBudgeted,
                'percentage_used' => $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 2) : 0,
            ],
        ];
    }
    
    /**
     * Get budget performance metrics
     */
    public function getBudgetPerformanceMetrics(string $period = 'month', ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $comparison = $this->compareActualVsBudget($period, $customStartDate, $customEndDate);
        
        if (!$comparison['has_budget']) {
            return [
                'has_budget' => false,
                'message' => $comparison['message'],
            ];
        }
        
        $overBudgetCount = 0;
        $underBudgetCount = 0;
        $onTrackCount = 0;
        $criticalOverages = [];
        
        foreach ($comparison['comparisons'] as $item) {
            if ($item['status'] === 'over_budget') {
                $overBudgetCount++;
                if ($item['percentage_used'] > 120) {
                    $criticalOverages[] = $item;
                }
            } elseif ($item['status'] === 'under_budget') {
                $underBudgetCount++;
            } else {
                $onTrackCount++;
            }
        }
        
        return [
            'has_budget' => true,
            'period' => $period,
            'metrics' => [
                'over_budget_count' => $overBudgetCount,
                'under_budget_count' => $underBudgetCount,
                'on_track_count' => $onTrackCount,
                'total_categories' => count($comparison['comparisons']),
                'critical_overages' => $criticalOverages,
                'unbudgeted_expense_count' => count($comparison['unbudgeted_expenses']),
            ],
            'summary' => $comparison['summary'],
        ];
    }
    
    /**
     * Get budget trend data for charts
     */
    public function getBudgetTrends(string $period = 'month', int $periods = 6, ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        $trends = [];
        $currentDate = Carbon::now();
        
        for ($i = $periods - 1; $i >= 0; $i--) {
            $periodDate = $this->getPeriodStartDate($period, $i);
            $periodLabel = $this->getPeriodLabel($period, $periodDate);
            
            $comparison = $this->compareActualVsBudget($period, $customStartDate, $customEndDate);
            
            $trends[] = [
                'period' => $periodLabel,
                'budgeted' => $comparison['summary']['total_budgeted'] ?? 0,
                'actual' => $comparison['summary']['total_actual'] ?? 0,
                'variance' => $comparison['summary']['total_variance'] ?? 0,
            ];
        }
        
        return $trends;
    }
    
    /**
     * Get variance status
     */
    private function getVarianceStatus(float $variance, string $itemType, float $percentageUsed): string
    {
        if ($itemType === 'expense') {
            // For expenses, over budget is bad
            if ($percentageUsed > 100) {
                return 'over_budget';
            } elseif ($percentageUsed >= 90) {
                return 'on_track';
            } else {
                return 'under_budget';
            }
        } else {
            // For revenue, under budget is bad
            if ($percentageUsed < 90) {
                return 'under_budget';
            } elseif ($percentageUsed >= 100) {
                return 'over_budget'; // Good for revenue
            } else {
                return 'on_track';
            }
        }
    }
    
    /**
     * Get period dates
     */
    private function getPeriodDates(string $period, ?string $customStartDate = null, ?string $customEndDate = null): array
    {
        // If custom dates are provided, use them
        if ($period === 'custom' && $customStartDate && $customEndDate) {
            return [
                'start' => $customStartDate,
                'end' => $customEndDate,
            ];
        }
        
        $now = Carbon::now();
        
        return match($period) {
            'today' => [
                'start' => $now->copy()->startOfDay()->format('Y-m-d'),
                'end' => $now->copy()->endOfDay()->format('Y-m-d'),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek()->format('Y-m-d'),
                'end' => $now->copy()->endOfWeek()->format('Y-m-d'),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $now->copy()->endOfMonth()->format('Y-m-d'),
            ],
            'quarter' => [
                'start' => $now->copy()->startOfQuarter()->format('Y-m-d'),
                'end' => $now->copy()->endOfQuarter()->format('Y-m-d'),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear()->format('Y-m-d'),
                'end' => $now->copy()->endOfYear()->format('Y-m-d'),
            ],
            default => [
                'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $now->copy()->endOfMonth()->format('Y-m-d'),
            ],
        };
    }
    
    /**
     * Get period start date for trends
     */
    private function getPeriodStartDate(string $period, int $periodsAgo): Carbon
    {
        $now = Carbon::now();
        
        return match($period) {
            'week' => $now->copy()->subWeeks($periodsAgo)->startOfWeek(),
            'month' => $now->copy()->subMonths($periodsAgo)->startOfMonth(),
            'quarter' => $now->copy()->subQuarters($periodsAgo)->startOfQuarter(),
            'year' => $now->copy()->subYears($periodsAgo)->startOfYear(),
            default => $now->copy()->subMonths($periodsAgo)->startOfMonth(),
        };
    }
    
    /**
     * Get period label for display
     */
    private function getPeriodLabel(string $period, Carbon $date): string
    {
        return match($period) {
            'week' => $date->format('M d'),
            'month' => $date->format('M Y'),
            'quarter' => 'Q' . $date->quarter . ' ' . $date->format('Y'),
            'year' => $date->format('Y'),
            default => $date->format('M Y'),
        };
    }
    
    /**
     * Get expense transaction types
     */
    private function getExpenseTypes(): array
    {
        return [
            // Platform operational expenses (from CMS)
            'marketing_expense',
            'office_expense',
            'travel_expense',
            'infrastructure_expense',
            'legal_expense',
            'professional_fees',
            'utilities_expense',
            'general_expense',
            
            // Member payouts (platform expenses)
            'commission_earned',      // Commissions paid to members
            'profit_share',           // Profit sharing paid to members
            'lgr_earned',            // LGR rewards paid to members
            'lgr_manual_award',      // Manual LGR awards paid to members
            'withdrawal',            // Withdrawals processed
            'loan_disbursement',     // Loans given to members
            'shop_credit_allocation', // Shop credits allocated
            
            // Refunds and reversals
            'loan_repayment',        // Actually revenue, but tracked separately
        ];
    }
    
    /**
     * Get revenue transaction types
     */
    private function getRevenueTypes(): array
    {
        return [
            // Member deposits
            'wallet_topup',
            'deposit',
            
            // Product sales
            'starter_kit_purchase',
            'starter_kit_upgrade',
            'starter_kit_gift',
            'shop_purchase',
            'marketplace_purchase',
            'learning_pack_purchase',
            
            // Service payments
            'subscription_payment',
            'workshop_payment',
            'coaching_payment',
            'service_payment',
            'growbuilder_payment',
            
            // Loan repayments (money coming back)
            'loan_repayment',
        ];
    }
    
    /**
     * Map transaction type to budget category
     */
    private function mapTransactionTypeToCategory(string $transactionType): string
    {
        return match($transactionType) {
            // Platform operational expenses
            'marketing_expense' => 'Marketing',
            'office_expense' => 'Office Expenses',
            'travel_expense' => 'Travel',
            'infrastructure_expense' => 'Infrastructure',
            'legal_expense' => 'Legal & Compliance',
            'professional_fees' => 'Professional Services',
            'utilities_expense' => 'Utilities',
            'general_expense' => 'General Expenses',
            
            // Member payouts (platform expenses)
            'commission_earned' => 'Commissions',
            'profit_share' => 'Profit Sharing',
            'lgr_earned', 'lgr_manual_award' => 'LGR Awards',
            'withdrawal' => 'Withdrawals',
            'loan_disbursement' => 'Loan Disbursements',
            'shop_credit_allocation' => 'Shop Credits',
            
            // Member deposits (revenue)
            'wallet_topup', 'deposit' => 'Wallet Top-ups',
            
            // Product sales (revenue)
            'starter_kit_purchase' => 'Starter Kits',
            'starter_kit_upgrade' => 'Starter Kit Upgrades',
            'starter_kit_gift' => 'Starter Kit Gifts',
            'shop_purchase' => 'Shop Sales',
            'marketplace_purchase' => 'Marketplace Sales',
            'learning_pack_purchase' => 'Learning Packs',
            
            // Service payments (revenue)
            'subscription_payment' => 'Subscriptions',
            'workshop_payment' => 'Workshops',
            'coaching_payment' => 'Coaching',
            'service_payment' => 'Services',
            'growbuilder_payment' => 'GrowBuilder',
            
            // Loan repayments (revenue)
            'loan_repayment' => 'Loan Repayments',
            
            // Default fallback
            default => ucwords(str_replace('_', ' ', $transactionType)),
        };
    }
}
