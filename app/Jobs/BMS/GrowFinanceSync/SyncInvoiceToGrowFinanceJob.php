<?php

namespace App\Jobs\CMS\GrowFinanceSync;

use App\Domain\CMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInvoiceToGrowFinanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $invoiceId
    ) {
        $this->onQueue('growfinance-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(GrowFinanceSyncService $syncService): void
    {
        $invoice = InvoiceModel::find($this->invoiceId);

        if (!$invoice) {
            Log::warning("Invoice #{$this->invoiceId} not found for GrowFinance sync");
            return;
        }

        try {
            $syncService->sync($invoice);
        } catch (\Exception $e) {
            Log::error("Failed to sync invoice #{$this->invoiceId} to GrowFinance", [
                'invoice_id' => $this->invoiceId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Invoice sync job failed after all retries", [
            'invoice_id' => $this->invoiceId,
            'error' => $exception->getMessage(),
        ]);
    }
}
