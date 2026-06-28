<?php

declare(strict_types=1);

namespace App\Domain\CMS\Services\GrowFinanceSync;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseSyncHandler
{
    public function __construct(
        private AccountingService $accountingService,
        private AccountMappingService $mappingService,
        private SyncStatusService $statusService
    ) {}

    /**
     * Sync expense to GrowFinance
     */
    public function sync(ExpenseModel $expense): void
    {
        // 1. Idempotency check
        if ($this->statusService->isSynced($expense)) {
            Log::info("Expense #{$expense->id} already synced, skipping");
            return;
        }

        try {
            DB::transaction(function () use ($expense) {
                // 2. Get account mappings
                $expenseAccount = $this->mappingService->getAccount(
                    $expense->company_id,
                    'expense',
                    $expense->category
                );

                if (!$expenseAccount) {
                    // Fall back to default expense account
                    $expenseAccount = $this->mappingService->getAccount(
                        $expense->company_id,
                        'expense',
                        'Other'
                    );
                }

                $vatAccount = $this->mappingService->getAccount(
                    $expense->company_id,
                    'expense',
                    'vat_receivable'
                );

                $cashAccount = $this->mappingService->getCashAccount(
                    $expense->company_id,
                    $expense->payment_method ?? 'bank_transfer'
                );

                if (!$expenseAccount || !$cashAccount) {
                    throw new \Exception('Account mappings not configured');
                }

                // 3. Build journal entry lines
                $lines = [];

                // Debit: Expense Account
                $lines[] = [
                    'account_id' => $expenseAccount->id,
                    'debit_amount' => $expense->amount,
                    'credit_amount' => 0,
                    'description' => $expense->description ?? $expense->category,
                ];

                // Debit: VAT Receivable (if applicable)
                if (isset($expense->vat_amount) && $expense->vat_amount > 0 && $vatAccount) {
                    $lines[] = [
                        'account_id' => $vatAccount->id,
                        'debit_amount' => $expense->vat_amount,
                        'credit_amount' => 0,
                        'description' => 'VAT on expense',
                    ];
                }

                // Credit: Cash/Bank Account
                $totalAmount = $expense->amount + ($expense->vat_amount ?? 0);
                $lines[] = [
                    'account_id' => $cashAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $totalAmount,
                    'description' => 'Payment made',
                ];

                // 4. Create journal entry in GrowFinance
                $journalEntry = $this->accountingService->createJournalEntry(
                    businessId: $expense->company_id,
                    description: "CMS Expense #{$expense->id} - {$expense->category}",
                    lines: $lines,
                    reference: "CMS-EXP-{$expense->id}",
                    createdBy: $expense->created_by ?? null
                );

                // 5. Post the entry
                $this->accountingService->postJournalEntry($journalEntry);

                // 6. Log success
                $this->statusService->logSuccess($expense, $journalEntry->id);

                Log::info("Successfully synced expense #{$expense->id} to GrowFinance", [
                    'expense_id' => $expense->id,
                    'journal_entry_id' => $journalEntry->id,
                ]);
            });
        } catch (\Exception $e) {
            // Log failure
            $this->statusService->logFailure($expense, $e->getMessage());

            Log::error("Failed to sync expense #{$expense->id} to GrowFinance", [
                'expense_id' => $expense->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync multiple expenses in bulk
     */
    public function bulkSync(array $expenseIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach ($expenseIds as $expenseId) {
            $expense = ExpenseModel::find($expenseId);

            if (!$expense) {
                $results['skipped']++;
                continue;
            }

            try {
                $this->sync($expense);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }
}
