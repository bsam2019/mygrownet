<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(
        private AccountingService $accountingService
    ) {}
    public function profitLoss(Request $request): Response
    {
        $businessId = $request->user()->id;
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $income = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $expenses = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $expensesByCategory = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        return Inertia::render('GrowFinance/Reports/ProfitLoss', [
            'income' => (float) $income,
            'expenses' => (float) $expenses,
            'netProfit' => (float) ($income - $expenses),
            'expensesByCategory' => $expensesByCategory,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function balanceSheet(Request $request): Response
    {
        $businessId = $request->user()->id;
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->orderBy('code')
            ->get();

        // Group assets
        $assetAccounts = $accounts->where('type', AccountType::ASSET->value);
        $currentAssets = $assetAccounts->whereIn('category', ['current_asset', 'bank', 'cash', 'accounts_receivable'])->values();
        $fixedAssets = $assetAccounts->whereIn('category', ['fixed_asset'])->values();
        
        // If no category set, put in current assets
        $uncategorizedAssets = $assetAccounts->whereNull('category')->values();
        $currentAssets = $currentAssets->merge($uncategorizedAssets);

        // Group liabilities
        $liabilityAccounts = $accounts->where('type', AccountType::LIABILITY->value);
        $currentLiabilities = $liabilityAccounts->whereIn('category', ['current_liability', 'accounts_payable'])->values();
        $longTermLiabilities = $liabilityAccounts->whereIn('category', ['long_term_liability'])->values();
        
        $uncategorizedLiabilities = $liabilityAccounts->whereNull('category')->values();
        $currentLiabilities = $currentLiabilities->merge($uncategorizedLiabilities);

        // Equity accounts
        $equityAccounts = $accounts->where('type', AccountType::EQUITY->value)->values();

        // Calculate retained earnings (income - expenses)
        $totalIncome = GrowFinanceInvoiceModel::forBusiness($businessId)->sum('amount_paid');
        $totalExpenses = GrowFinanceExpenseModel::forBusiness($businessId)->sum('amount');
        $retainedEarnings = $totalIncome - $totalExpenses;

        $totalAssets = $currentAssets->sum('current_balance') + $fixedAssets->sum('current_balance');
        $totalLiabilities = $currentLiabilities->sum('current_balance') + $longTermLiabilities->sum('current_balance');
        $totalEquity = $equityAccounts->sum('current_balance') + $retainedEarnings;

        return Inertia::render('GrowFinance/Reports/BalanceSheet', [
            'data' => [
                'assets' => [
                    'current' => $currentAssets,
                    'fixed' => $fixedAssets,
                    'total' => (float) $totalAssets,
                ],
                'liabilities' => [
                    'current' => $currentLiabilities,
                    'longTerm' => $longTermLiabilities,
                    'total' => (float) $totalLiabilities,
                ],
                'equity' => [
                    'accounts' => $equityAccounts,
                    'retainedEarnings' => (float) $retainedEarnings,
                    'total' => (float) $totalEquity,
                ],
            ],
            'asOfDate' => $asOfDate,
        ]);
    }

    public function cashFlow(Request $request): Response
    {
        $businessId = $request->user()->id;
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Operating activities
        $cashFromSales = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $expensesByCategory = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $operatingItems = [
            ['name' => 'Cash received from customers', 'amount' => (float) $cashFromSales],
        ];

        foreach ($expensesByCategory as $expense) {
            $operatingItems[] = [
                'name' => $expense->category ?? 'Other expenses',
                'amount' => -1 * (float) $expense->total,
            ];
        }

        $operatingTotal = collect($operatingItems)->sum('amount');

        // Get cash account balance
        $cashAccount = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('category', 'cash')
            ->orWhere('category', 'bank')
            ->first();

        $openingBalance = $cashAccount ? (float) $cashAccount->current_balance - $operatingTotal : 0;
        $closingBalance = $cashAccount ? (float) $cashAccount->current_balance : $operatingTotal;

        return Inertia::render('GrowFinance/Reports/CashFlow', [
            'data' => [
                'operating' => [
                    'items' => $operatingItems,
                    'total' => $operatingTotal,
                ],
                'investing' => [
                    'items' => [],
                    'total' => 0,
                ],
                'financing' => [
                    'items' => [],
                    'total' => 0,
                ],
                'netChange' => $operatingTotal,
                'openingBalance' => $openingBalance,
                'closingBalance' => $closingBalance,
            ],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function trialBalance(Request $request): Response
    {
        $businessId = $request->user()->id;
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));

        $trialBalance = $this->accountingService->getTrialBalance($businessId);

        return Inertia::render('GrowFinance/Reports/TrialBalance', [
            'balances' => $trialBalance['balances'],
            'totalDebits' => $trialBalance['total_debits'],
            'totalCredits' => $trialBalance['total_credits'],
            'isBalanced' => $trialBalance['is_balanced'],
            'asOfDate' => $asOfDate,
        ]);
    }

    public function generalLedger(Request $request): Response
    {
        $businessId = $request->user()->id;
        $accountId = $request->get('account_id');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->orderBy('code')
            ->get();

        $selectedAccount = $accountId ? $accounts->firstWhere('id', $accountId) : null;
        $ledgerEntries = [];

        if ($selectedAccount) {
            $entries = GrowFinanceJournalEntryModel::forBusiness($businessId)
                ->with(['lines' => function ($query) use ($selectedAccount) {
                    $query->where('account_id', $selectedAccount->id);
                }])
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->where('is_posted', true)
                ->orderBy('entry_date')
                ->orderBy('id')
                ->get();

            $runningBalance = (float) $selectedAccount->opening_balance;

            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    $debit = (float) $line->debit_amount;
                    $credit = (float) $line->credit_amount;

                    // Calculate running balance based on account type
                    if ($selectedAccount->type->isDebitNormal()) {
                        $runningBalance += ($debit - $credit);
                    } else {
                        $runningBalance += ($credit - $debit);
                    }

                    $ledgerEntries[] = [
                        'id' => $entry->id,
                        'date' => $entry->entry_date,
                        'entry_number' => $entry->entry_number,
                        'description' => $entry->description,
                        'reference' => $entry->reference,
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance' => $runningBalance,
                    ];
                }
            }
        }

        return Inertia::render('GrowFinance/Reports/GeneralLedger', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'ledgerEntries' => $ledgerEntries,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $businessId = $request->user()->id;
        $format = $request->get('format', 'csv');

        $data = match ($type) {
            'profit-loss' => $this->getProfitLossData($businessId, $request),
            'balance-sheet' => $this->getBalanceSheetData($businessId, $request),
            'trial-balance' => $this->getTrialBalanceData($businessId),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        if ($format === 'csv') {
            return $this->exportCsv($type, $data);
        }

        // For PDF, we'd need a PDF library - returning CSV for now
        return $this->exportCsv($type, $data);
    }

    private function getProfitLossData(int $businessId, Request $request): array
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $income = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $expensesByCategory = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $rows = [
            ['Profit & Loss Statement'],
            ['Period: ' . $startDate . ' to ' . $endDate],
            [''],
            ['INCOME'],
            ['Sales Revenue', number_format((float) $income, 2)],
            ['Total Income', number_format((float) $income, 2)],
            [''],
            ['EXPENSES'],
        ];

        $totalExpenses = 0;
        foreach ($expensesByCategory as $expense) {
            $rows[] = [$expense->category ?? 'Other', number_format((float) $expense->total, 2)];
            $totalExpenses += (float) $expense->total;
        }

        $rows[] = ['Total Expenses', number_format($totalExpenses, 2)];
        $rows[] = [''];
        $rows[] = ['NET PROFIT', number_format((float) $income - $totalExpenses, 2)];

        return $rows;
    }

    private function getBalanceSheetData(int $businessId, Request $request): array
    {
        $accounts = GrowFinanceAccountModel::forBusiness($businessId)
            ->active()
            ->orderBy('code')
            ->get();

        $rows = [
            ['Balance Sheet'],
            ['As of: ' . Carbon::now()->format('Y-m-d')],
            [''],
            ['ASSETS'],
        ];

        $totalAssets = 0;
        foreach ($accounts->where('type', AccountType::ASSET->value) as $account) {
            $rows[] = [$account->name, number_format((float) $account->current_balance, 2)];
            $totalAssets += (float) $account->current_balance;
        }
        $rows[] = ['Total Assets', number_format($totalAssets, 2)];

        $rows[] = [''];
        $rows[] = ['LIABILITIES'];
        $totalLiabilities = 0;
        foreach ($accounts->where('type', AccountType::LIABILITY->value) as $account) {
            $rows[] = [$account->name, number_format((float) $account->current_balance, 2)];
            $totalLiabilities += (float) $account->current_balance;
        }
        $rows[] = ['Total Liabilities', number_format($totalLiabilities, 2)];

        $rows[] = [''];
        $rows[] = ['EQUITY'];
        $totalEquity = 0;
        foreach ($accounts->where('type', AccountType::EQUITY->value) as $account) {
            $rows[] = [$account->name, number_format((float) $account->current_balance, 2)];
            $totalEquity += (float) $account->current_balance;
        }
        $rows[] = ['Total Equity', number_format($totalEquity, 2)];

        return $rows;
    }

    private function getTrialBalanceData(int $businessId): array
    {
        $trialBalance = $this->accountingService->getTrialBalance($businessId);

        $rows = [
            ['Trial Balance'],
            ['As of: ' . Carbon::now()->format('Y-m-d')],
            [''],
            ['Account', 'Debit', 'Credit'],
        ];

        foreach ($trialBalance['balances'] as $item) {
            $rows[] = [
                $item['account']->name,
                $item['debit'] > 0 ? number_format($item['debit'], 2) : '',
                $item['credit'] > 0 ? number_format($item['credit'], 2) : '',
            ];
        }

        $rows[] = [
            'TOTALS',
            number_format($trialBalance['total_debits'], 2),
            number_format($trialBalance['total_credits'], 2),
        ];

        return $rows;
    }

    private function exportCsv(string $type, array $data): StreamedResponse
    {
        $filename = $type . '-' . Carbon::now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
