<?php

namespace App\Listeners\BMS\GrowFinanceSync;

use App\Domain\Core\Listeners\InboxAware;
use App\Domain\Core\Services\InboxService;
use App\Events\BMS\InvoiceCreated;
use App\Domain\BMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Jobs\BMS\GrowFinanceSync\SyncInvoiceToGrowFinanceJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class InvoiceCreatedListener implements ShouldQueue
{
    use InteractsWithQueue, InboxAware;

    public function __construct(
        private GrowFinanceSyncService $syncService,
        private InboxService $inbox,
    ) {}

    public function handle(InvoiceCreated $event): void
    {
        $this->processWithInbox(
            eventId: (string) $event->invoice->id,
            eventName: 'bms.invoice.created.v1',
            payload: ['invoice_id' => $event->invoice->id, 'company_id' => $event->invoice->company_id],
            publisher: 'bms',
            handler: function (array $payload) use ($event) {
                $invoice = $event->invoice;

                if (!$this->syncService->isSyncEnabled($invoice->company_id)) {
                    Log::debug("GrowFinance sync not enabled for company {$invoice->company_id}, skipping invoice sync");
                    return;
                }

                SyncInvoiceToGrowFinanceJob::dispatch($invoice->id)
                    ->delay(now()->addSeconds(5));

                Log::info("Dispatched GrowFinance sync job for invoice #{$invoice->id}");
            },
        );
    }

    public function failed(InvoiceCreated $event, \Throwable $exception): void
    {
        $this->inbox->markFailed((string) $event->invoice->id);

        Log::error("Failed to dispatch GrowFinance sync for invoice #{$event->invoice->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
