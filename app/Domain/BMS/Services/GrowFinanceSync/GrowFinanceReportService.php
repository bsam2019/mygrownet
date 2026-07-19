<?php

namespace App\Domain\CMS\Services\GrowFinanceSync;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceJournalEntryModel;
use App\Infrastructure\Persistence\Eloquent\CMS\GrowFinanceSyncConfigModel;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use Carbon\Carbon;

/**
 * Service for generating CMS reports powered by GrowFinance data
 * 
 * This service provides enhanced financial reports when GrowFinance module is enabled.
 * It pulls data from the GrowFinance accounting system to provide:
 * - Complete Balance Sheet
 * - Cash Flow Statement
 * - General Ledger
 * - Trial Balance
 */
class GrowFinanceReportService
{
    /**
     * Check if GrowFinance module is enabled for a company
     */
    public function isEnabled(int $companyId): bool
    {
        $config = GrowFinanceSyncConfigModel::where('company_id', $companyId)->first();
        return $config && $config->is_enabled;
    }

    /**
     * Get Balance Sheet data from GrowFinance
     * 
     * @param int $companyId CMS company ID (which is also the GrowFinance business ID)
     * @param string|null $asOfDate Date for the balance sheet (defaults to today)
     * @return array Balance sheet data with assets, liabilities, and equity
     */
    public function getBalanceSheet(int $companyId, ?string $asOfDate = null): array
    {
        if (!$this->isEnabled($companyId)) {
            return $this->getEmptyBalanceSheet();
        }

        $asOfDate = $asOfDate ?? Carbon::now()->format('Y-m-d');

        $accounts = GrowFinanceAccountModel::forBusiness($companyId)
            ->active()
            ->orderBy('code')
            ->get();

        // Group assets
        $assetAccounts = $accounts->where('type', AccountType::ASSET->value);
        $currentAssets = $assetAccounts->whereIn('category', ['current_asset', 'bank', 'cash', 'accounts_receivable'])->values();
        $fixedAssets = $assetAccounts->whereIn('category', ['fixed_asset'])->values();
        
        // Uncategorized assets go to current assets
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
        $totalIncome = GrowFinanceInvoiceModel::forBusiness($companyId)->sum('amount_paid');
        $totalExpenses = GrowFinanceExpenseModel::forBusiness($companyId)->sum('amount');
        $retainedEarnings = $totalIncome - $totalExpenses;

        $totalCurrentAssets = $currentAssets->sum('current_balance');
        $totalFixedAssets = $fixedAssets->sum('current_balance');
        $totalAssets = $totalCurrentAssets + $totalFixedAssets;
        
        $totalCurrentLiabilities = $currentLiabilities->sum('current_balance');
        $totalLongTermLiabilities = $longTermLiabilities->sum('current_balance');
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;
        
        $totalEquity = $equityAccounts->sum('current_balance') + $retainedEarnings;

        return [
            'as_of_date' => $asOfDate,
            'assets' => [
                'current' => [
                    'accounts' => $currentAssets->map(fn($a) => [
                        'id' => $a->id,
                        'code' => $a->code,
                        'name' => $a->name,
                        'balance' => (float) $a->current_balance,
                    ])->toArray(),
                    'total' => (float) $totalCurrentAssets,
                ],
                'fixed' => [
                    'accounts' => $fixedAssets->map(fn($a) => [
                        'id' => $a->id,
                        'code' => $a->code,
                        'name' => $a->name,
                        'balance' => (float) $a->current_balance,
                    ])->toArray(),
                    'total' => (float) $totalFixedAssets,
                ],
                'total' => (float) $totalAssets,
            ],
            'liabilities' => [
                'current' => [
                    'accounts' => $currentLiabilities->map(fn($a) => [
                        'id' => $a->id,
                        'code' => $a->code,
                        'name' => $a->name,
                        'balance' => (float) $a->current_balance,
                    ])->toArray(),
                    'total' => (float) $totalCurrentLiabilities,
                ],
                'long_term' => [
                    'accounts' => $longTermLiabilities->map(fn($a) => [
                        'id' => $a->id,
                        'code' => $a->code,
                        'name' => $a->name,
                        'balance' => (float) $a->current_balance,
                    ])->toArray(),
                    'total' => (float) $totalLongTermLiabilities,
                ],
                'total' => (float) $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equityAccounts->map(fn($a) => [
                    'id' => $a->id,
                    'code' => $a->code,
                    'name' => $a->name,
                    'balance' => (float) $a->current_balance,
                ])->toArray(),
                'retained_earnings' => (float) $retainedEarnings,
                'total' => (float) $totalEquity,
            ],
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * Get Cash Flow Statement data from GrowFinance
     * 
     * @param int $companyId CMS company ID
     * @param string $startDate Start date for the period
     * @param string $endDate End date for the period
     * @return array Cash flow statement data
     */
    public function getCashFlowStatement(int $companyId, string $startDate, string $endDate): array
    {
        if (!$this->isEnabled($companyId)) {
            return $this->getEmptyCashFlow();
        }

        // Operating activities
        $cashFromSales = GrowFinanceInvoiceModel::forBusiness($companyId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('amount_paid');

        $expensesByCategory = GrowFinanceExpenseModel::forBusiness($companyId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $operatingItems = [
            ['name' => 'Cash received from customers', 'amount' => (float) $cashFromSales],
        ];

        foreach ($expensesByCategory as $expense) {
            $operatingItems[] = [
                'name' => 'Payments: ' . ($expense->category ?? 'Other expenses'),
                'amount' => -1 * (float) $expense->total,
            ];
        }

        $operatingTotal = collect($operatingItems)->sum('amount');

        // Get cash account balance
        $cashAccounts = GrowFinanceAccountModel::forBusiness($companyId)
            ->where(function($query) {
                $query->where('category', 'cash')
                      ->orWhere('category', 'bank');
            })
            ->get();

        $closingBalance = $cashAccounts->sum('current_balance');
        $openingBalance = $closingBalance - $operatingTotal;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'operating_activities' => [
                'items' => $operatingItems,
                'total' => (float) $operatingTotal,
            ],
            'investing_activities' => [
                'items' => [],
                'total' => 0.0,
            ],
            'financing_activities' => [
                'items' => [],
                'total' => 0.0,
            ],
            'net_change' => (float) $operatingTotal,
            'opening_balance' => (float) $openingBalance,
            'closing_balance' => (float) $closingBalance,
        ];
    }

    /**
     * Get General Ledger data from GrowFinance
     * 
     * @param int $companyId CMS company ID
     * @param int|null $accountId Specific account ID (null for all accounts)
     * @param string $startDate Start date for the period
     * @param string $endDate End date for the period
     * @return array General ledger data
     */
    public function getGeneralLedger(int $companyId, ?int $accountId, string $startDate, string $endDate): array
    {
        if (!$this->isEnabled($companyId)) {
            return $this->getEmptyGeneralLedger();
        }

        $accounts = GrowFinanceAccountModel::forBusiness($companyId)
            ->active()
            ->orderBy('code')
            ->get();

        $selectedAccount = $accountId ? $accounts->firstWhere('id', $accountId) : null;
        $ledgerEntries = [];

        if ($selectedAccount) {
            $entries = GrowFinanceJournalEntryModel::forBusiness($companyId)
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

        return [
            'accounts' => $accounts->map(fn($a) => [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'type' => $a->type->value,
                'current_balance' => (float) $a->current_balance,
            ])->toArray(),
            'selected_account' => $selectedAccount ? [
                'id' => $selectedAccount->id,
                'code' => $selectedAccount->code,
                'name' => $selectedAccount->name,
                'type' => $selectedAccount->type->value,
                'opening_balance' => (float) $selectedAccount->opening_balance,
                'current_balance' => (float) $selectedAccount->current_balance,
            ] : null,
            'entries' => $ledgerEntries,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get Trial Balance data from GrowFinance
     * 
     * @param int $companyId CMS company ID
     * @param string|null $asOfDate Date for the trial balance
     * @return array Trial balance data
     */
    public function getTrialBalance(int $companyId, ?string $asOfDate = null): array
    {
        if (!$this->isEnabled($companyId)) {
            return $this->getEmptyTrialBalance();
        }

        $asOfDate = $asOfDate ?? Carbon::now()->format('Y-m-d');

        $accounts = GrowFinanceAccountModel::forBusiness($companyId)
            ->active()
            ->orderBy('code')
            ->get();

        $balances = [];
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($accounts as $account) {
            $balance = (float) $account->current_balance;
            
            // Determine if balance goes in debit or credit column
            if ($account->type->isDebitNormal()) {
                $debit = $balance >= 0 ? $balance : 0;
                $credit = $balance < 0 ? abs($balance) : 0;
            } else {
                $debit = $balance < 0 ? abs($balance) : 0;
                $credit = $balance >= 0 ? $balance : 0;
            }

            $balances[] = [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type->value,
                ],
                'debit' => $debit,
                'credit' => $credit,
            ];

            $totalDebits += $debit;
            $totalCredits += $credit;
        }

        return [
            'as_of_date' => $asOfDate,
            'balances' => $balances,
            'total_debits' => (float) $totalDebits,
            'total_credits' => (float) $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    /**
     * Get empty balance sheet structure (when GrowFinance is disabled)
     */
    private function getEmptyBalanceSheet(): array
    {
        return [
            'as_of_date' => Carbon::now()->format('Y-m-d'),
            'assets' => [
                'current' => ['accounts' => [], 'total' => 0.0],
                'fixed' => ['accounts' => [], 'total' => 0.0],
                'total' => 0.0,
            ],
            'liabilities' => [
                'current' => ['accounts' => [], 'total' => 0.0],
                'long_term' => ['accounts' => [], 'total' => 0.0],
                'total' => 0.0,
            ],
            'equity' => [
                'accounts' => [],
                'retained_earnings' => 0.0,
                'total' => 0.0,
            ],
            'is_balanced' => true,
            'growfinance_disabled' => true,
        ];
    }

    /**
     * Get empty cash flow structure (when GrowFinance is disabled)
     */
    private function getEmptyCashFlow(): array
    {
        return [
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            'operating_activities' => ['items' => [], 'total' => 0.0],
            'investing_activities' => ['items' => [], 'total' => 0.0],
            'financing_activities' => ['items' => [], 'total' => 0.0],
            'net_change' => 0.0,
            'opening_balance' => 0.0,
            'closing_balance' => 0.0,
            'growfinance_disabled' => true,
        ];
    }

    /**
     * Get empty general ledger structure (when GrowFinance is disabled)
     */
    private function getEmptyGeneralLedger(): array
    {
        return [
            'accounts' => [],
            'selected_account' => null,
            'entries' => [],
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            'growfinance_disabled' => true,
        ];
    }

    /**
     * Get empty trial balance structure (when GrowFinance is disabled)
     */
    private function getEmptyTrialBalance(): array
    {
        return [
            'as_of_date' => Carbon::now()->format('Y-m-d'),
            'balances' => [],
            'total_debits' => 0.0,
            'total_credits' => 0.0,
            'is_balanced' => true,
            'growfinance_disabled' => true,
        ];
    }
}
