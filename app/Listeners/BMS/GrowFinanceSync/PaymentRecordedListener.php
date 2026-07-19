<?php

namespace App\Listeners\CMS\GrowFinanceSync;

use App\Events\CMS\PaymentRecorded;
use App\Domain\CMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Jobs\CMS\GrowFinanceSync\SyncPaymentToGrowFinanceJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PaymentRecordedListener implements ShouldQueue
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
    public function handle(PaymentRecorded $event): void
    {
        $payment = $event->payment;

        // Check if GrowFinance module is enabled for this company
        if (!$this->syncService->isSyncEnabled($payment->company_id)) {
            Log::debug("GrowFinance sync not enabled for company {$payment->company_id}, skipping payment sync");
            return;
        }

        // Dispatch job with 5-second delay for stability
        SyncPaymentToGrowFinanceJob::dispatch($payment->id)
            ->delay(now()->addSeconds(5));

        Log::info("Dispatched GrowFinance sync job for payment #{$payment->id}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentRecorded $event, \Throwable $exception): void
    {
        Log::error("Failed to dispatch GrowFinance sync for payment #{$event->payment->id}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
