<?php

namespace App\Services;

use App\Services\PlatformLoanService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;

/**
 * Cash Flow Statement Service
 * 
 * Generates cash flow statements for MyGrowNet Platform.
 * Shows cash inflows and outflows categorized by activity type.
 */
class CashFlowStatementService
{
    public function __construct(
        private PlatformLoanService $loanService
    ) {}
    
    /**
     * Get complete cash flow statement
     */
    public function getCashFlowStatement(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'from' => $startDate->format('Y-m-d'),
                'to' => $endDate->format('Y-m-d'),
            ],
            'operating_activities' => $this->getOperatingActivities($startDate, $endDate),
            'investing_activities' => $this->getInvestingActivities($startDate, $endDate),
            'financing_activities' => $this->getFinancingActivities($startDate, $endDate),
        ];
    }
    
    /**
     * Get operating activities (day-to-day business operations)
     */
    private function getOperatingActivities(Carbon $startDate, Carbon $endDate): array
    {
        // Cash received from customers (revenue)
        $revenueReceived = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', [
                TransactionType::SUBSCRIPTION_PAYMENT->value,
                TransactionType::STARTER_KIT_PURCHASE->value,
                TransactionType::SHOP_PURCHASE->value,
                TransactionType::MARKETPLACE_PURCHASE->value,
                TransactionType::LEARNING_PACK_PURCHASE->value,
                TransactionType::SERVICE_PAYMENT->value,
                TransactionType::WORKSHOP_PAYMENT->value,
                TransactionType::COACHING_PAYMENT->value,
            ])
            ->sum(DB::raw('ABS(amount)'));
        
        // Interest received from loans
        $interestReceived = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('transaction_type', 'interest_income')
            ->sum(DB::raw('ABS(amount)'));
        
        // Cash paid to members (commissions, withdrawals, etc.)
        $commissionsPaid = DB::table('referral_commissions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('amount');
        
        $withdrawalsPaid = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('transaction_type', TransactionType::WITHDRAWAL->value)
            ->sum(DB::raw('ABS(amount)'));
        
        $profitSharesPaid = DB::table('profit_shares')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('amount');
        
        // Operating expenses
        $operatingExpenses = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->whereIn('transaction_type', [
                'marketing_expense',
                'office_expense',
                'travel_expense',
                'infrastructure_expense',
                'legal_expense',
                'professional_fees',
                'utilities_expense',
                'general_expense',
            ])
            ->sum(DB::raw('ABS(amount)'));
        
        $cashInflows = $revenueReceived + $interestReceived;
        $cashOutflows = $commissionsPaid + $withdrawalsPaid + $profitSharesPaid + $operatingExpenses;
        $netOperatingCash = $cashInflows - $cashOutflows;
        
        return [
            'cash_inflows' => [
                'revenue_received' => $revenueReceived,
                'interest_received' => $interestReceived,
                'total' => $cashInflows,
            ],
            'cash_outflows' => [
                'commissions_paid' => $commissionsPaid,
                'withdrawals_paid' => $withdrawalsPaid,
                'profit_shares_paid' => $profitSharesPaid,
                'operating_expenses' => $operatingExpenses,
                'total' => $cashOutflows,
            ],
            'net_cash_from_operations' => $netOperatingCash,
        ];
    }
    
    /**
     * Get investing activities (loans, assets, etc.)
     */
    private function getInvestingActivities(Carbon $startDate, Carbon $endDate): array
    {
        // Loan activities
        $loanCashFlow = $this->loanService->getCashFlowData($startDate, $endDate);
        
        $cashInflows = $loanCashFlow['principal_repayments'];
        $cashOutflows = $loanCashFlow['loans_disbursed'];
        $netInvestingCash = $cashInflows - $cashOutflows;
        
        return [
            'cash_inflows' => [
                'loan_repayments' => $loanCashFlow['principal_repayments'],
                'total' => $cashInflows,
            ],
            'cash_outflows' => [
                'loans_disbursed' => $loanCashFlow['loans_disbursed'],
                'total' => $cashOutflows,
            ],
            'net_cash_from_investing' => $netInvestingCash,
        ];
    }
    
    /**
     * Get financing activities (capital, equity, etc.)
     */
    private function getFinancingActivities(Carbon $startDate, Carbon $endDate): array
    {
        // Member deposits (financing inflow)
        $deposits = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', TransactionStatus::COMPLETED->value)
            ->where('transaction_type', TransactionType::WALLET_TOPUP->value)
            ->sum('amount');
        
        // For now, no other financing activities
        $cashInflows = $deposits;
        $cashOutflows = 0;
        $netFinancingCash = $cashInflows - $cashOutflows;
        
        return [
            'cash_inflows' => [
                'member_deposits' => $deposits,
                'total' => $cashInflows,
            ],
            'cash_outflows' => [
                'total' => $cashOutflows,
            ],
            'net_cash_from_financing' => $netFinancingCash,
        ];
    }
    
    /**
     * Get cash flow summary
     */
    public function getSummary(Carbon $startDate, Carbon $endDate): array
    {
        $statement = $this->getCashFlowStatement($startDate, $endDate);
        
        $netCashChange = 
            $statement['operating_activities']['net_cash_from_operations'] +
            $statement['investing_activities']['net_cash_from_investing'] +
            $statement['financing_activities']['net_cash_from_financing'];
        
        return [
            'net_cash_from_operations' => $statement['operating_activities']['net_cash_from_operations'],
            'net_cash_from_investing' => $statement['investing_activities']['net_cash_from_investing'],
            'net_cash_from_financing' => $statement['financing_activities']['net_cash_from_financing'],
            'net_cash_change' => $netCashChange,
            'operating_cash_flow_ratio' => $statement['operating_activities']['cash_inflows']['total'] > 0
                ? $statement['operating_activities']['net_cash_from_operations'] / $statement['operating_activities']['cash_inflows']['total']
                : 0,
        ];
    }
}
