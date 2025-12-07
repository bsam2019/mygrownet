<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\PdfReportService;
use App\Domain\Module\Services\SubscriptionService;
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
        private AccountingService $accountingService,
        private SubscriptionService $subscriptionService,
        private PdfReportService $pdfReportService
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
        // Check if user can access this report
        if (!$this->subscriptionService->canAccessReport($request->user(), 'balance-sheet')) {
            return Inertia::render('GrowFinance/Reports/UpgradeRequired', [
                'reportName' => 'Balance Sheet',
                'requiredTier' => 'basic',
            ]);
        }

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
        // Check if user can access this report
        if (!$this->subscriptionService->canAccessReport($request->user(), 'trial-balance')) {
            return Inertia::render('GrowFinance/Reports/UpgradeRequired', [
                'reportName' => 'Trial Balance',
                'requiredTier' => 'basic',
            ]);
        }

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
        // Check if user can access this report
        if (!$this->subscriptionService->canAccessReport($request->user(), 'general-ledger')) {
            return Inertia::render('GrowFinance/Reports/UpgradeRequired', [
                'reportName' => 'General Ledger',
                'requiredTier' => 'basic',
            ]);
        }

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

    public function export(Request $request, string $type): StreamedResponse|HttpResponse
    {
        $businessId = $request->user()->id;
        $format = $request->get('format', 'csv');

        // For PDF export, check subscription
        if ($format === 'pdf') {
            $check = $this->pdfReportService->canExportPdf($request->user());
            if (!$check['allowed']) {
                return back()->with('error', $check['reason']);
            }

            return $this->exportPdf($request, $type);
        }

        $data = match ($type) {
            'profit-loss' => $this->getProfitLossData($businessId, $request),
            'balance-sheet' => $this->getBalanceSheetData($businessId, $request),
            'trial-balance' => $this->getTrialBalanceData($businessId),
            'cash-flow' => $this->getCashFlowCsvData($businessId, $request),
            'general-ledger' => $this->getGeneralLedgerCsvData($businessId, $request),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        return $this->exportCsv($type, $data);
    }

    /**
     * Export report as PDF
     */
    private function exportPdf(Request $request, string $type): HttpResponse
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $asOfDate = $request->get('as_of_date', Carbon::now()->format('Y-m-d'));
        $accountId = $request->get('account_id');

        $pdf = match ($type) {
            'profit-loss' => $this->pdfReportService->generateProfitLoss($user, $startDate, $endDate),
            'balance-sheet' => $this->pdfReportService->generateBalanceSheet($user, $asOfDate),
            'cash-flow' => $this->pdfReportService->generateCashFlow($user, $startDate, $endDate),
            'trial-balance' => $this->pdfReportService->generateTrialBalance($user, $asOfDate),
            'general-ledger' => $this->pdfReportService->generateGeneralLedger($user, $startDate, $endDate, $accountId),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        $filename = $type . '-' . Carbon::now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get Cash Flow data for CSV export
     */
    private function getCashFlowCsvData(int $businessId, Request $request): array
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $cashFromSales = GrowFinanceInvoiceModel::forBusiness($businessId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $expensesByCategory = GrowFinanceExpenseModel::forBusiness($businessId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $totalExpenses = $expensesByCategory->sum('total');

        $rows = [
            ['Cash Flow Statement'],
            ['Period: ' . $startDate . ' to ' . $endDate],
            [''],
            ['OPERATING ACTIVITIES'],
            ['Cash received from customers', number_format((float) $cashFromSales, 2)],
        ];

        foreach ($expensesByCategory as $expense) {
            $rows[] = ['Payment: ' . ($expense->category ?? 'Other'), '-' . number_format((float) $expense->total, 2)];
        }

        $netCashFlow = (float) $cashFromSales - (float) $totalExpenses;
        $rows[] = [''];
        $rows[] = ['Net Cash Flow', number_format($netCashFlow, 2)];

        return $rows;
    }

    /**
     * Get General Ledger data for CSV export
     */
    private function getGeneralLedgerCsvData(int $businessId, Request $request): array
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $accountId = $request->get('account_id');

        $rows = [
            ['General Ledger'],
            ['Period: ' . $startDate . ' to ' . $endDate],
            [''],
        ];

        $query = GrowFinanceAccountModel::forBusiness($businessId)->active()->orderBy('code');
        if ($accountId) {
            $query->where('id', $accountId);
        }
        $accounts = $query->get();

        foreach ($accounts as $account) {
            $rows[] = [''];
            $rows[] = ['Account: ' . $account->name . ' (' . $account->code . ')'];
            $rows[] = ['Date', 'Reference', 'Description', 'Debit', 'Credit', 'Balance'];

            $entries = GrowFinanceJournalEntryModel::forBusiness($businessId)
                ->with(['lines' => function ($query) use ($account) {
                    $query->where('account_id', $account->id);
                }])
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->where('is_posted', true)
                ->orderBy('entry_date')
                ->get();

            $runningBalance = (float) $account->opening_balance;

            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    $debit = (float) $line->debit_amount;
                    $credit = (float) $line->credit_amount;

                    if ($account->type->isDebitNormal()) {
                        $runningBalance += ($debit - $credit);
                    } else {
                        $runningBalance += ($credit - $debit);
                    }

                    $rows[] = [
                        $entry->entry_date,
                        $entry->reference ?? '',
                        $entry->description,
                        $debit > 0 ? number_format($debit, 2) : '',
                        $credit > 0 ? number_format($credit, 2) : '',
                        number_format($runningBalance, 2),
                    ];
                }
            }
        }

        return $rows;
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
