<?php

namespace App\Jobs\BMS\GrowFinanceSync;

use App\Domain\BMS\Services\GrowFinanceSync\GrowFinanceSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 3600; // 1 hour for bulk operations

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $companyId,
        public ?string $fromDate = null
    ) {
        $this->onQueue('growfinance-sync');
    }

    /**
     * Execute the job.
     */
    public function handle(GrowFinanceSyncService $syncService): void
    {
        Log::info("Starting bulk sync for company #{$this->companyId}", [
            'company_id' => $this->companyId,
            'from_date' => $this->fromDate,
        ]);

        try {
            $results = $syncService->bulkSyncHistorical($this->companyId, $this->fromDate);

            Log::info("Bulk sync completed for company #{$this->companyId}", [
                'company_id' => $this->companyId,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error("Bulk sync failed for company #{$this->companyId}", [
                'company_id' => $this->companyId,
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
        Log::error("Bulk sync job failed", [
            'company_id' => $this->companyId,
            'error' => $exception->getMessage(),
        ]);
    }
}
