<?php

namespace App\Domain\GrowFinance\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PdfReportService
{
    public function canExportPdf(User $user): array
    {
        return ['allowed' => true];
    }

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

    private function getProfitLossData(int $businessId, string $startDate, string $endDate): array
    {
        $revenue = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($startDate, $endDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->whereBetween('je.date', [$startDate, $endDate]);
            })
            ->where('a.business_id', $businessId)
            ->where(function ($q) {
                $q->where('a.type', 'income')->orWhere('a.type', 'revenue');
            })
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(jl.credit_amount) - SUM(jl.debit_amount), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code')
            ->having('balance', '!=', 0)
            ->get();

        $expenses = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($startDate, $endDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->whereBetween('je.date', [$startDate, $endDate]);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'expense')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(jl.debit_amount) - SUM(jl.credit_amount), 0) as balance')
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

    private function getBalanceSheetData(int $businessId, string $asOfDate): array
    {
        $assets = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->where('je.date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'asset')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.statement_category',
                DB::raw('COALESCE(SUM(jl.debit_amount) - SUM(jl.credit_amount), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.statement_category')
            ->get();

        $liabilities = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->where('je.date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'liability')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.statement_category',
                DB::raw('COALESCE(SUM(jl.credit_amount) - SUM(jl.debit_amount), 0) as balance')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.statement_category')
            ->get();

        $equity = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->where('je.date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->where('a.type', 'equity')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                DB::raw('COALESCE(SUM(jl.credit_amount) - SUM(jl.debit_amount), 0) as balance')
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

    private function getCashFlowData(int $businessId, string $startDate, string $endDate): array
    {
        $cashAccounts = DB::table('growfinance_accounts')
            ->where('business_id', $businessId)
            ->where('category', 'Cash')
            ->pluck('id');

        $openingBalance = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccounts)
            ->where('je.status', 'posted')
            ->where('je.date', '<', $startDate)
            ->selectRaw('COALESCE(SUM(jl.debit_amount) - SUM(jl.credit_amount), 0) as balance')
            ->value('balance') ?? 0;

        $inflows = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccounts)
            ->where('je.status', 'posted')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('jl.debit_amount', '>', 0)
            ->sum('jl.debit_amount');

        $outflows = (float) DB::table('growfinance_journal_lines as jl')
            ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $cashAccounts)
            ->where('je.status', 'posted')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('jl.credit_amount', '>', 0)
            ->sum('jl.credit_amount');

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

    private function getTrialBalanceData(int $businessId, string $asOfDate): array
    {
        $accounts = DB::table('growfinance_accounts as a')
            ->leftJoin('growfinance_journal_lines as jl', 'a.id', '=', 'jl.account_id')
            ->leftJoin('growfinance_journal_entries as je', function ($join) use ($asOfDate) {
                $join->on('jl.journal_entry_id', '=', 'je.id')
                    ->where('je.status', '=', 'posted')
                    ->where('je.date', '<=', $asOfDate);
            })
            ->where('a.business_id', $businessId)
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.type',
                DB::raw('COALESCE(SUM(jl.debit_amount), 0) as total_debit'),
                DB::raw('COALESCE(SUM(jl.credit_amount), 0) as total_credit')
            )
            ->groupBy('a.id', 'a.name', 'a.code', 'a.type')
            ->having(DB::raw('COALESCE(SUM(jl.debit_amount), 0) + COALESCE(SUM(jl.credit_amount), 0)'), '>', 0)
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
            $openingBalance = (float) DB::table('growfinance_journal_lines as jl')
                ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->where('jl.account_id', $account->id)
                ->where('je.status', 'posted')
                ->where('je.date', '<', $startDate)
                ->selectRaw('COALESCE(SUM(jl.debit_amount) - SUM(jl.credit_amount), 0) as balance')
                ->value('balance') ?? 0;

            $transactions = DB::table('growfinance_journal_lines as jl')
                ->join('growfinance_journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
                ->where('jl.account_id', $account->id)
                ->where('je.status', 'posted')
                ->whereBetween('je.date', [$startDate, $endDate])
                ->select(
                    'je.date',
                    'je.reference',
                    'je.description',
                    'jl.debit_amount as debit',
                    'jl.credit_amount as credit',
                    'je.journal_number'
                )
                ->orderBy('je.date')
                ->get();

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
