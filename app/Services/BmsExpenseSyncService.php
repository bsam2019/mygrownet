<?php

namespace App\Services;

use App\Domain\BMS\Core\Services\BmsDataService;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseModel;
use App\Models\Transaction;
use App\Models\BMS\CmsSyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BmsExpenseSyncService
{
    public function __construct(
        private BmsDataService $bmsData,
    ) {}

    public function syncExpenseToTransaction(ExpenseModel $expense): ?Transaction
    {
        if ($expense->approval_status !== 'approved') {
            Log::info("Expense {$expense->id} not approved, skipping sync");
            return null;
        }

        if ($this->isAlreadySynced($expense->id)) {
            Log::info("Expense {$expense->id} already synced");
            return $this->getExistingTransaction($expense->id);
        }

        DB::beginTransaction();
        try {
            $syncLog = CmsSyncLog::create([
                'cms_entity_type' => 'expense',
                'cms_entity_id' => $expense->id,
                'sync_status' => 'pending',
            ]);

            $transactionType = $this->mapExpenseCategoryToTransactionType($expense);

            $transaction = Transaction::create([
                'user_id' => $expense->recordedBy->user_id ?? null,
                'transaction_type' => $transactionType,
                'amount' => -abs($expense->amount),
                'status' => TransactionStatus::COMPLETED->value,
                'description' => $this->buildTransactionDescription($expense),
                'transaction_source' => 'cms_expense',
                'reference_number' => $expense->expense_number,
                'cms_expense_id' => $expense->id,
                'cms_reference_type' => 'expense',
                'cms_reference_id' => $expense->id,
                'created_at' => $expense->expense_date,
                'updated_at' => now(),
            ]);

            $syncLog->update([
                'transaction_id' => $transaction->id,
                'sync_status' => 'synced',
                'synced_at' => now(),
            ]);

            DB::commit();

            Log::info("Successfully synced expense {$expense->id} to transaction {$transaction->id}");

            return $transaction;

        } catch (Exception $e) {
            DB::rollBack();

            if (isset($syncLog)) {
                $syncLog->update([
                    'sync_status' => 'failed',
                    'sync_error' => $e->getMessage(),
                ]);
            }

            Log::error("Failed to sync expense {$expense->id}: {$e->getMessage()}");

            throw $e;
        }
    }

    public function isAlreadySynced(int $expenseId): bool
    {
        return CmsSyncLog::where('cms_entity_type', 'expense')
            ->where('cms_entity_id', $expenseId)
            ->where('sync_status', 'synced')
            ->exists();
    }

    public function getExistingTransaction(int $expenseId): ?Transaction
    {
        $syncLog = CmsSyncLog::where('cms_entity_type', 'expense')
            ->where('cms_entity_id', $expenseId)
            ->where('sync_status', 'synced')
            ->first();

        return $syncLog?->transaction;
    }

    private function mapExpenseCategoryToTransactionType(ExpenseModel $expense): string
    {
        $categoryMappings = [
            'marketing' => TransactionType::MARKETING_EXPENSE->value,
            'advertising' => TransactionType::MARKETING_EXPENSE->value,
            'office' => TransactionType::OFFICE_EXPENSE->value,
            'office supplies' => TransactionType::OFFICE_EXPENSE->value,
            'travel' => TransactionType::TRAVEL_EXPENSE->value,
            'infrastructure' => TransactionType::INFRASTRUCTURE_EXPENSE->value,
            'hosting' => TransactionType::INFRASTRUCTURE_EXPENSE->value,
            'software' => TransactionType::INFRASTRUCTURE_EXPENSE->value,
            'legal' => TransactionType::LEGAL_EXPENSE->value,
            'professional fees' => TransactionType::PROFESSIONAL_FEES->value,
            'utilities' => TransactionType::UTILITIES_EXPENSE->value,
        ];

        $categoryName = strtolower($expense->category->name ?? '');

        if (isset($categoryMappings[$categoryName])) {
            return $categoryMappings[$categoryName];
        }

        foreach ($categoryMappings as $keyword => $type) {
            if (str_contains($categoryName, $keyword)) {
                return $type;
            }
        }

        return TransactionType::GENERAL_EXPENSE->value;
    }

    private function buildTransactionDescription(ExpenseModel $expense): string
    {
        $parts = [
            $expense->category->name ?? 'Expense',
            $expense->description,
        ];

        if ($expense->job) {
            $parts[] = "Job: {$expense->job->job_number}";
        }

        return implode(' - ', array_filter($parts));
    }

    private function getModuleIdForExpense(ExpenseModel $expense): ?int
    {
        return null;
    }

    public function retryFailedSyncs(): array
    {
        $failedSyncs = CmsSyncLog::where('cms_entity_type', 'expense')
            ->where('sync_status', 'failed')
            ->get();

        $results = [
            'total' => $failedSyncs->count(),
            'success' => 0,
            'failed' => 0,
        ];

        foreach ($failedSyncs as $syncLog) {
            try {
                $expense = $this->bmsData->findExpense($syncLog->cms_entity_id);

                if (!$expense) {
                    $syncLog->update([
                        'sync_error' => 'Expense not found',
                    ]);
                    $results['failed']++;
                    continue;
                }

                $this->syncExpenseToTransaction($expense);
                $results['success']++;

            } catch (Exception $e) {
                $results['failed']++;
                Log::error("Retry failed for expense {$syncLog->cms_entity_id}: {$e->getMessage()}");
            }
        }

        return $results;
    }

    public function getSyncStatistics(): array
    {
        return [
            'total_synced' => CmsSyncLog::where('cms_entity_type', 'expense')
                ->where('sync_status', 'synced')
                ->count(),
            'pending' => CmsSyncLog::where('cms_entity_type', 'expense')
                ->where('sync_status', 'pending')
                ->count(),
            'failed' => CmsSyncLog::where('cms_entity_type', 'expense')
                ->where('sync_status', 'failed')
                ->count(),
            'last_sync' => CmsSyncLog::where('cms_entity_type', 'expense')
                ->where('sync_status', 'synced')
                ->latest('synced_at')
                ->value('synced_at'),
        ];
    }
}
