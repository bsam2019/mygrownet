<?php

namespace App\Listeners\CMS\GrowFinanceSync;

use App\Events\CMS\InvoiceCreated;
use App\Domain\CMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Jobs\CMS\GrowFinanceSync\SyncInvoiceToGrowFinanceJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class InvoiceCreatedListener implements ShouldQueue
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
    public function handle(InvoiceCreated $event): void
    {
        $invoice = $event->invoice;

        // Check if GrowFinance module is enabled for this company
        if (!$this->syncService->isSyncEnabled($invoice->company_id)) {
            Log::debug("GrowFinance sync not enabled for company {$invoice->company_id}, skipping invoice sync");
            return;
        }

        // Dispatch job with 5-second delay for stability
        SyncInvoiceToGrowFinanceJob::dispatch($invoice->id)
            ->delay(now()->addSeconds(5));

        Log::info("Dispatched GrowFinance sync job for invoice #{$invoice->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(InvoiceCreated $event, \Throwable $exception): void
    {
        Log::error("Failed to dispatch GrowFinance sync for invoice #{$event->invoice->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
