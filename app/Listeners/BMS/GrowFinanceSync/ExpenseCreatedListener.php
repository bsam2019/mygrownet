<?php

namespace App\Listeners\BMS\GrowFinanceSync;

use App\Events\BMS\ExpenseCreated;
use App\Domain\BMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Jobs\BMS\GrowFinanceSync\SyncExpenseToGrowFinanceJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ExpenseCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private GrowFinanceSyncService $syncService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ExpenseCreated $event): void
    {
        $expense = $event->expense;

        // Check if GrowFinance module is enabled for this company
        if (!$this->syncService->isSyncEnabled($expense->company_id)) {
            Log::debug("GrowFinance sync not enabled for company {$expense->company_id}, skipping expense sync");
            return;
        }

        // Dispatch job with 5-second delay for stability
        SyncExpenseToGrowFinanceJob::dispatch($expense->id)
            ->delay(now()->addSeconds(5));

        Log::info("Dispatched GrowFinance sync job for expense #{$expense->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExpenseCreated $event, \Throwable $exception): void
    {
        Log::error("Failed to dispatch GrowFinance sync for expense #{$event->expense->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
