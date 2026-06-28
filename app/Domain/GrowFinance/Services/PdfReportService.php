<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PdfReportService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Check if user can export PDF reports
     */
    public function canExportPdf(User $user): array
    {
        if (!$this->subscriptionService->canPerformAction($user, 'pdf_export')) {
            return [
                'allowed' => false,
                'reason' => 'PDF export is available on Professional plan and above.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Generate Profit & Loss PDF
     */
    public function generateProfitLoss(
        User $user,
        string $startDate,
        string $endDate
    ): \Barryvdh\DomPDF\PDF {
        $data = $this->getProfitLossData($user->id, $startDate, $endDate);

        return Pdf::loadView('pdf.growfinance.profit-loss', [
            'user' => $user,
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Generate Balance Sheet PDF
     */
    public function generateBalanceSheet(User $user, string $asOfDate): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getBalanceSheetData($user->id, $asOfDate);

        return Pdf::loadView('pdf.growfinance.balance-sheet', [
            'user' => $user,
            'data' => $data,
            'asOfDate' => $asOfDate,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Generate Cash Flow PDF
     */
    public function generateCashFlow(
        User $user,
        string $startDate,
        string $endDate
    ): \Barryvdh\DomPDF\PDF {
        $data = $this->getCashFlowData($user->id, $startDate, $endDate);

        return Pdf::loadView('pdf.growfinance.cash-flow', [
            'user' => $user,
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Generate Trial Balance PDF
     */
    public function generateTrialBalance(User $user, string $asOfDate): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getTrialBalanceData($user->id, $asOfDate);

        return Pdf::loadView('pdf.growfinance.trial-balance', [
            'user' => $user,
            'data' => $data,
            'asOfDate' => $asOfDate,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Generate General Ledger PDF
     */
    public function generateGeneralLedger(
        User $user,
        string $startDate,
        string $endDate,
        ?int $accountId = null
    ): \Barryvdh\DomPDF\PDF {
        $data = $this->getGeneralLedgerData($user->id, $startDate, $endDate, $accountId);

        return Pdf::loadView('pdf.growfinance.general-ledger', [
            'user' => $user,
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->format('F j, Y g:i A'),
        ]);
    }

    /**
     * Get Profit & Loss data
     */
    private function getProfitLossData(int $businessId, string $startDate, string $endDate): array
    {
        // Revenue accounts (type = 'revenue')
        $revenue = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($startDate, $endDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->whereBetween('je.entry_date', [$startDate, $endDate]);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'revenue')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(je.credit) - SUM(je.debit), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code')
            ->having('balance', '!=', 0)
            ->get();

        // Expense accounts (type = 'expense')
        $expenses = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($startDate, $endDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->whereBetween('je.entry_date', [$startDate, $endDate]);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'expense')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(je.debit) - SUM(je.credit), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code')
            ->having('balance', '!=', 0)
            ->get();

        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $netIncome,
        ];
    }

    /**
     * Get Balance Sheet data
     */
    private function getBalanceSheetData(int $businessId, string $asOfDate): array
    {
        // Assets
        $assets = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->where('je.entry_date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'asset')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.sub_type',
                DB::raw('COALESCE(SUM(je.debit) - SUM(je.credit), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.sub_type')
            ->get();

        // Liabilities
        $liabilities = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->where('je.entry_date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'liability')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.sub_type',
                DB::raw('COALESCE(SUM(je.credit) - SUM(je.debit), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.sub_type')
            ->get();

        // Equity
        $equity = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->where('je.entry_date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'equity')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(je.credit) - SUM(je.debit), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code')
            ->get();

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
        ];
    }

    /**
     * Get Cash Flow data
     */
    private function getCashFlowData(int $businessId, string $startDate, string $endDate): array
    {
        // Cash accounts
        $cashAccounts = DB::table('growfinance_accounts')
            ->where('business_id', $businessId)
            ->where('sub_type', 'cash')
            ->pluck('id');

        // Opening balance
        $openingBalance = DB::table('growfinance_journal_entries')
            ->whereIn('account_id', $cashAccounts)
            ->where('entry_date', '<', $startDate)
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as balance')
            ->value('balance') ?? 0;

        // Cash inflows
        $inflows = DB::table('growfinance_journal_entries')
            ->whereIn('account_id', $cashAccounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('debit', '>', 0)
            ->sum('debit');

        // Cash outflows
        $outflows = DB::table('growfinance_journal_entries')
            ->whereIn('account_id', $cashAccounts)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('credit', '>', 0)
            ->sum('credit');

        $netCashFlow = $inflows - $outflows;
        $closingBalance = $openingBalance + $netCashFlow;

        return [
            'openingBalance' => $openingBalance,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'netCashFlow' => $netCashFlow,
            'closingBalance' => $closingBalance,
        ];
    }

    /**
     * Get Trial Balance data
     */
    private function getTrialBalanceData(int $businessId, string $asOfDate): array
    {
        $accounts = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('a.id', '=', 'je.account_id')
                    ->where('je.entry_date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.type',
                DB::raw('COALESCE(SUM(je.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(je.credit), 0) as total_credit')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.type')
            ->having(DB::raw('COALESCE(SUM(je.debit), 0) + COALESCE(SUM(je.credit), 0)'), '>', 0)
            ->orderBy('a.code')
            ->get();

        $totalDebits = $accounts->sum('total_debit');
        $totalCredits = $accounts->sum('total_credit');

        return [
            'accounts' => $accounts,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'isBalanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    /**
     * Get General Ledger data
     */
    private function getGeneralLedgerData(
        int $businessId,
        string $startDate,
        string $endDate,
        ?int $accountId = null
    ): array {
        $query = DB::table('growfinance_accounts as a')
            ->where('a.business_id', $businessId);

        if ($accountId) {
            $query->where('a.id', $accountId);
        }

        $accounts = $query->orderBy('a.code')->get();

        $ledger = [];

        foreach ($accounts as $account) {
            // Opening balance
            $openingBalance = DB::table('growfinance_journal_entries')
                ->where('account_id', $account->id)
                ->where('entry_date', '<', $startDate)
                ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as balance')
                ->value('balance') ?? 0;

            // Transactions
            $transactions = DB::table('growfinance_journal_entries as je')
                ->join('growfinance_transactions as t', 'je.transaction_id', '=', 't.id')
                ->where('je.account_id', $account->id)
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->select(
                    'je.entry_date',
                    't.reference',
                    't.description',
                    'je.debit',
                    'je.credit'
                )
                ->orderBy('je.entry_date')
                ->get();

            // Calculate running balance
            $runningBalance = $openingBalance;
            $transactionsWithBalance = $transactions->map(function ($tx) use (&$runningBalance) {
                $runningBalance += $tx->debit - $tx->credit;
                $tx->balance = $runningBalance;
                return $tx;
            });

            $ledger[] = [
                'account' => $account,
                'openingBalance' => $openingBalance,
                'transactions' => $transactionsWithBalance,
                'closingBalance' => $runningBalance,
            ];
        }

        return ['ledger' => $ledger];
    }
}
