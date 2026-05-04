<?php

declare(strict_types=1);

namespace App\Domain\CMS\Services\GrowFinanceSync;

use App\Infrastructure\Persistence\Eloquent\CMS\GrowFinanceSyncConfigModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GrowFinanceSyncService
{
    // Circuit breaker state
    private static array $circuitState = [];
    private const FAILURE_THRESHOLD = 10;
    private const CIRCUIT_TIMEOUT = 300; // 5 minutes

    public function __construct(
        private InvoiceSyncHandler $invoiceSyncHandler,
        private ExpenseSyncHandler $expenseSyncHandler,
        private PaymentSyncHandler $paymentSyncHandler,
        private SyncStatusService $statusService,
        private AccountMappingService $mappingService
    ) {}

    /**
     * Sync any CMS entity to GrowFinance
     */
    public function sync(Model $entity): void
    {
        $companyId = $entity->company_id;

        // Check if sync is enabled
        if (!$this->isSyncEnabled($companyId)) {
            Log::debug("GrowFinance sync disabled for company {$companyId}");
            return;
        }

        // Check circuit breaker
        if ($this->isCircuitOpen($companyId)) {
            Log::warning("GrowFinance sync circuit open for company {$companyId}, skipping");
            return;
        }

        try {
            match (true) {
                $entity instanceof InvoiceModel => $this->invoiceSyncHandler->sync($entity),
                $entity instanceof ExpenseModel => $this->expenseSyncHandler->sync($entity),
                $entity instanceof PaymentModel => $this->paymentSyncHandler->sync($entity),
                default => throw new \InvalidArgumentException('Unsupported entity type for sync'),
            };

            $this->recordSuccess($companyId);
        } catch (\Exception $e) {
            $this->recordFailure($companyId);
            
            // Check if we should open circuit
            if ($this->shouldOpenCircuit($companyId)) {
                $this->openCircuit($companyId);
                Log::error("GrowFinance sync circuit opened for company {$companyId} due to high failure rate");
            }

            throw $e;
        }
    }

    /**
     * Check if sync is enabled for a company
     */
    public function isSyncEnabled(int $companyId): bool
    {
        $config = $this->getConfig($companyId);
        return $config && $config->isSyncEnabled();
    }

    /**
     * Get sync configuration for a company
     */
    public function getConfig(int $companyId): ?GrowFinanceSyncConfigModel
    {
        return GrowFinanceSyncConfigModel::forCompany($companyId)->first();
    }

    /**
     * Enable sync for a company
     */
    public function enableSync(int $companyId, ?int $growfinanceBusinessId = null): GrowFinanceSyncConfigModel
    {
        $config = GrowFinanceSyncConfigModel::updateOrCreate(
            ['company_id' => $companyId],
            [
                'is_enabled' => true,
                'growfinance_business_id' => $growfinanceBusinessId ?? $companyId,
                'auto_sync' => true,
                'sync_invoices' => true,
                'sync_expenses' => true,
                'sync_payments' => true,
            ]
        );

        // Create default account mappings
        $this->mappingService->createDefaultMappings($companyId);

        Log::info("GrowFinance sync enabled for company {$companyId}");

        return $config;
    }

    /**
     * Disable sync for a company
     */
    public function disableSync(int $companyId): void
    {
        $config = $this->getConfig($companyId);
        
        if ($config) {
            $config->update(['is_enabled' => false]);
            Log::info("GrowFinance sync disabled for company {$companyId}");
        }
    }

    /**
     * Retry failed syncs for a company
     */
    public function retryFailedSyncs(int $companyId, int $limit = 50): array
    {
        $failedSyncs = $this->statusService->getFailedSyncs($companyId, $limit);
        
        $results = [
            'total' => $failedSyncs->count(),
            'success' => 0,
            'failed' => 0,
        ];

        foreach ($failedSyncs as $syncLog) {
            try {
                // Find the entity
                $entity = $this->findEntity($syncLog->cms_entity_type, $syncLog->cms_entity_id);
                
                if (!$entity) {
                    $results['failed']++;
                    continue;
                }

                // Reset sync status
                $this->statusService->resetSyncStatus($entity);

                // Retry sync
                $this->sync($entity);
                
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Failed to retry sync for {$syncLog->cms_entity_type} #{$syncLog->cms_entity_id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Bulk sync historical data
     */
    public function bulkSyncHistorical(int $companyId, ?string $fromDate = null): array
    {
        $results = [
            'invoices' => ['success' => 0, 'failed' => 0],
            'expenses' => ['success' => 0, 'failed' => 0],
            'payments' => ['success' => 0, 'failed' => 0],
        ];

        // Sync invoices
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->where('growfinance_synced', false)
            ->when($fromDate, fn($q) => $q->where('created_at', '>=', $fromDate))
            ->pluck('id')
            ->toArray();

        $results['invoices'] = $this->invoiceSyncHandler->bulkSync($invoices);

        // Sync expenses
        $expenses = ExpenseModel::where('company_id', $companyId)
            ->where('growfinance_synced', false)
            ->when($fromDate, fn($q) => $q->where('created_at', '>=', $fromDate))
            ->pluck('id')
            ->toArray();

        $results['expenses'] = $this->expenseSyncHandler->bulkSync($expenses);

        // Sync payments
        $payments = PaymentModel::where('company_id', $companyId)
            ->where('growfinance_synced', false)
            ->when($fromDate, fn($q) => $q->where('created_at', '>=', $fromDate))
            ->pluck('id')
            ->toArray();

        $results['payments'] = $this->paymentSyncHandler->bulkSync($payments);

        return $results;
    }

    /**
     * Get sync health for a company
     */
    public function getHealth(int $companyId): array
    {
        return $this->statusService->getHealth($companyId);
    }

    /**
     * Get sync statistics for a company
     */
    public function getStats(int $companyId): array
    {
        return $this->statusService->getStats($companyId);
    }

    // Circuit Breaker Methods

    private function isCircuitOpen(int $companyId): bool
    {
        if (!isset(self::$circuitState[$companyId])) {
            return false;
        }

        $state = self::$circuitState[$companyId];
        
        if ($state['open'] && time() - $state['opened_at'] > self::CIRCUIT_TIMEOUT) {
            // Circuit timeout expired, close it
            $this->closeCircuit($companyId);
            return false;
        }

        return $state['open'];
    }

    private function shouldOpenCircuit(int $companyId): bool
    {
        if (!isset(self::$circuitState[$companyId])) {
            return false;
        }

        $failures = self::$circuitState[$companyId]['failures'] ?? 0;
        return $failures >= self::FAILURE_THRESHOLD;
    }

    private function openCircuit(int $companyId): void
    {
        self::$circuitState[$companyId] = [
            'open' => true,
            'opened_at' => time(),
            'failures' => 0,
        ];
    }

    private function closeCircuit(int $companyId): void
    {
        self::$circuitState[$companyId] = [
            'open' => false,
            'failures' => 0,
        ];
    }

    private function recordSuccess(int $companyId): void
    {
        if (isset(self::$circuitState[$companyId])) {
            self::$circuitState[$companyId]['failures'] = 0;
        }
    }

    private function recordFailure(int $companyId): void
    {
        if (!isset(self::$circuitState[$companyId])) {
            self::$circuitState[$companyId] = [
                'open' => false,
                'failures' => 0,
            ];
        }

        self::$circuitState[$companyId]['failures']++;
    }

    private function findEntity(string $entityType, int $entityId): ?Model
    {
        return match ($entityType) {
            'invoice' => InvoiceModel::find($entityId),
            'expense' => ExpenseModel::find($entityId),
            'payment' => PaymentModel::find($entityId),
            default => null,
        };
    }
}
