<?php

namespace App\Listeners;

use App\Services\CmsExpenseSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Sync Approved Expense to Transaction Listener
 * 
 * Listens for expense approval events and syncs them to the transactions table.
 * Runs asynchronously via queue for better performance.
 */
class SyncApprovedExpenseToTransaction implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private CmsExpenseSyncService $syncService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Check if event has expense property
        if (!isset($event->expense)) {
            Log::warning('ExpenseApproved event missing expense property');
            return;
        }

        $expense = $event->expense;

        try {
            // Sync expense to transaction
            $transaction = $this->syncService->syncExpenseToTransaction($expense);

            if ($transaction) {
                Log::info("Expense {$expense->id} synced to transaction {$transaction->id}");
            }

        } catch (\Exception $e) {
            Log::error("Failed to sync expense {$expense->id}: {$e->getMessage()}");
            
            // Re-throw to mark job as failed (will retry based on queue config)
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(object $event, \Throwable $exception): void
    {
        Log::error('SyncApprovedExpenseToTransaction failed permanently', [
            'expense_id' => $event->expense->id ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
