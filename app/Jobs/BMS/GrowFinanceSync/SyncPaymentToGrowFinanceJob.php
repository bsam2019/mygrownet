<?php

namespace App\Jobs\BMS\GrowFinanceSync;

use App\Domain\BMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPaymentToGrowFinanceJob implements ShouldQueue
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
        public int $paymentId
    ) {
        $this->onQueue('growfinance-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(GrowFinanceSyncService $syncService): void
    {
        $payment = PaymentModel::find($this->paymentId);

        if (!$payment) {
            Log::warning("Payment #{$this->paymentId} not found for GrowFinance sync");
            return;
        }

        try {
            $syncService->sync($payment);
        } catch (\Exception $e) {
            Log::error("Failed to sync payment #{$this->paymentId} to GrowFinance", [
                'payment_id' => $this->paymentId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Payment sync job failed after all retries", [
            'payment_id' => $this->paymentId,
            'error' => $exception->getMessage(),
        ]);
    }
}
