<?php

namespace App\Services;

use App\Services\PlatformLoanService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Balance Sheet Service
 * 
 * Generates balance sheet reports for MyGrowNet Platform.
 * Shows assets, liabilities, and equity at a point in time.
 */
class BalanceSheetService
{
    public function __construct(
        private PlatformLoanService $loanService
    ) {}
    
    /**
     * Get complete balance sheet
     */
    public function getBalanceSheet(?Carbon $asOfDate = null): array
    {
        $date = $asOfDate ?? now();
        
        return [
            'as_of_date' => $date->format('Y-m-d'),
            'assets' => $this->getAssets($date),
            'liabilities' => $this->getLiabilities($date),
            'equity' => $this->getEquity($date),
        ];
    }
    
    /**
     * Get all assets
     */
    private function getAssets(Carbon $date): array
    {
        // Current Assets
        $cash = $this->getCashBalance($date);
        $loansReceivable = $this->loanService->getBalanceSheetData($date);
        $walletBalances = $this->getWalletBalances($date);
        
        $currentAssets = [
            'cash' => [
                'label' => 'Cash and Cash Equivalents',
                'amount' => $cash,
            ],
            'loans_receivable' => [
                'label' => 'Loans Receivable',
                'amount' => $loansReceivable['total_loans_receivable'],
                'breakdown' => [
                    'current' => $loansReceivable['current_loans'],
                    'overdue_30_days' => $loansReceivable['breakdown_by_risk']['30_days'],
                    'overdue_60_days' => $loansReceivable['breakdown_by_risk']['60_days'],
                    'overdue_90_days' => $loansReceivable['breakdown_by_risk']['90_days'],
                    'defaulted' => $loansReceivable['breakdown_by_risk']['default'],
                ],
            ],
        ];
        
        $totalCurrentAssets = $cash + $loansReceivable['total_loans_receivable'];
        
        return [
            'current_assets' => $currentAssets,
            'total_current_assets' => $totalCurrentAssets,
            'total_assets' => $totalCurrentAssets, // For now, only current assets
        ];
    }
    
    /**
     * Get all liabilities
     */
    private function getLiabilities(Carbon $date): array
    {
        // Current Liabilities
        $walletBalances = $this->getWalletBalances($date);
        $unpaidCommissions = $this->getUnpaidCommissions($date);
        $unpaidProfitShares = $this->getUnpaidProfitShares($date);
        
        $currentLiabilities = [
            'wallet_balances' => [
                'label' => 'Member Wallet Balances',
                'amount' => $walletBalances,
                'note' => 'Money owed to members',
            ],
            'unpaid_commissions' => [
                'label' => 'Unpaid Commissions',
                'amount' => $unpaidCommissions,
            ],
            'unpaid_profit_shares' => [
                'label' => 'Unpaid Profit Shares',
                'amount' => $unpaidProfitShares,
            ],
        ];
        
        $totalCurrentLiabilities = $walletBalances + $unpaidCommissions + $unpaidProfitShares;
        
        return [
            'current_liabilities' => $currentLiabilities,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'total_liabilities' => $totalCurrentLiabilities, // For now, only current liabilities
        ];
    }
    
    /**
     * Get equity
     */
    private function getEquity(Carbon $date): array
    {
        $assets = $this->getAssets($date);
        $liabilities = $this->getLiabilities($date);
        
        $totalEquity = $assets['total_assets'] - $liabilities['total_liabilities'];
        
        return [
            'retained_earnings' => [
                'label' => 'Retained Earnings',
                'amount' => $totalEquity,
                'note' => 'Accumulated profits',
            ],
            'total_equity' => $totalEquity,
        ];
    }
    
    /**
     * Get cash balance (simplified - actual cash in bank/hand)
     */
    private function getCashBalance(Carbon $date): float
    {
        // This would typically come from bank account integration
        // For now, calculate from transactions
        $totalCashIn = DB::table('transactions')
            ->where('created_at', '<=', $date)
            ->where('status', 'completed')
            ->where('transaction_type', 'wallet_topup')
            ->sum('amount');
        
        $totalCashOut = DB::table('transactions')
            ->where('created_at', '<=', $date)
            ->where('status', 'completed')
            ->where('transaction_type', 'withdrawal')
            ->sum(DB::raw('ABS(amount)'));
        
        return $totalCashIn - $totalCashOut;
    }
    
    /**
     * Get total wallet balances (liability - money owed to members)
     */
    private function getWalletBalances(Carbon $date): float
    {
        // Sum of all member wallet balances
        $walletBalances = DB::table('transactions')
            ->where('created_at', '<=', $date)
            ->where('status', 'completed')
            ->select('user_id', DB::raw('SUM(amount) as balance'))
            ->groupBy('user_id')
            ->get()
            ->sum('balance');
        
        return max(0, $walletBalances); // Only positive balances are liabilities
    }
    
    /**
     * Get unpaid commissions
     */
    private function getUnpaidCommissions(Carbon $date): float
    {
        return DB::table('referral_commissions')
            ->where('created_at', '<=', $date)
            ->where('status', 'pending')
            ->sum('amount');
    }
    
    /**
     * Get unpaid profit shares
     */
    private function getUnpaidProfitShares(Carbon $date): float
    {
        return DB::table('profit_shares')
            ->where('created_at', '<=', $date)
            ->where('status', 'pending')
            ->sum('amount');
    }
    
    /**
     * Get balance sheet summary
     */
    public function getSummary(?Carbon $asOfDate = null): array
    {
        $balanceSheet = $this->getBalanceSheet($asOfDate);
        
        return [
            'total_assets' => $balanceSheet['assets']['total_assets'],
            'total_liabilities' => $balanceSheet['liabilities']['total_liabilities'],
            'total_equity' => $balanceSheet['equity']['total_equity'],
            'debt_to_equity_ratio' => $balanceSheet['equity']['total_equity'] > 0 
                ? $balanceSheet['liabilities']['total_liabilities'] / $balanceSheet['equity']['total_equity']
                : 0,
            'current_ratio' => $balanceSheet['liabilities']['total_current_liabilities'] > 0
                ? $balanceSheet['assets']['total_current_assets'] / $balanceSheet['liabilities']['total_current_liabilities']
                : 0,
        ];
    }
}
