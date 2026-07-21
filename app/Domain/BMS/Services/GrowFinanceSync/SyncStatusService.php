<?php

declare(strict_types=1);

namespace App\Domain\BMS\Services\GrowFinanceSync;

use App\Infrastructure\Persistence\Eloquent\BMS\GrowFinanceSyncLogModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SyncStatusService
{
    /**
     * Check if an entity is already synced
     */
    public function isSynced(Model $entity): bool
    {
        if (property_exists($entity, 'growfinance_synced')) {
            return (bool) $entity->growfinance_synced;
        }

        $log = $this->getLog($entity);
        return $log && $log->isSynced();
    }

    /**
     * Get sync log for an entity
     */
    public function getLog(Model $entity): ?GrowFinanceSyncLogModel
    {
        return GrowFinanceSyncLogModel::forEntity(
            $this->getEntityType($entity),
            $entity->id
        )->first();
    }

    /**
     * Create or update sync log
     */
    public function logSync(
        Model $entity,
        string $status,
        ?int $journalEntryId = null,
        ?string $errorMessage = null
    ): GrowFinanceSyncLogModel {
        $log = $this->getLog($entity);

        if ($log) {
            $log->update([
                'sync_status' => $status,
                'growfinance_journal_entry_id' => $journalEntryId,
                'last_sync_at' => now(),
                'error_message' => $errorMessage,
                'sync_attempt_count' => $status === 'failed' ? $log->sync_attempt_count + 1 : $log->sync_attempt_count,
            ]);
        } else {
            $log = GrowFinanceSyncLogModel::create([
                'company_id' => $entity->company_id,
                'cms_entity_type' => $this->getEntityType($entity),
                'cms_entity_id' => $entity->id,
                'sync_status' => $status,
                'growfinance_journal_entry_id' => $journalEntryId,
                'last_sync_at' => now(),
                'error_message' => $errorMessage,
                'sync_attempt_count' => $status === 'failed' ? 1 : 0,
            ]);
        }

        return $log;
    }

    /**
     * Log successful sync
     */
    public function logSuccess(Model $entity, int $journalEntryId): GrowFinanceSyncLogModel
    {
        // Update entity sync status
        if (property_exists($entity, 'growfinance_synced')) {
            $entity->update([
                'growfinance_synced' => true,
                'growfinance_journal_entry_id' => $journalEntryId,
            ]);
        }

        return $this->logSync($entity, 'synced', $journalEntryId);
    }

    /**
     * Log failed sync
     */
    public function logFailure(Model $entity, string $errorMessage): GrowFinanceSyncLogModel
    {
        return $this->logSync($entity, 'failed', null, $errorMessage);
    }

    /**
     * Log pending sync
     */
    public function logPending(Model $entity): GrowFinanceSyncLogModel
    {
        return $this->logSync($entity, 'pending');
    }

    /**
     * Get failed syncs for a company
     */
    public function getFailedSyncs(int $companyId, int $limit = 50): Collection
    {
        return GrowFinanceSyncLogModel::forCompany($companyId)
            ->failed()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending syncs for a company
     */
    public function getPendingSyncs(int $companyId, int $limit = 50): Collection
    {
        return GrowFinanceSyncLogModel::forCompany($companyId)
            ->pending()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get sync statistics for a company
     */
    public function getStats(int $companyId): array
    {
        $total = GrowFinanceSyncLogModel::forCompany($companyId)->count();
        $synced = GrowFinanceSyncLogModel::forCompany($companyId)->synced()->count();
        $failed = GrowFinanceSyncLogModel::forCompany($companyId)->failed()->count();
        $pending = GrowFinanceSyncLogModel::forCompany($companyId)->pending()->count();

        return [
            'total' => $total,
            'synced' => $synced,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($synced / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get sync health status
     */
    public function getHealth(int $companyId): array
    {
        $stats = $this->getStats($companyId);
        
        // Get recent failures (last hour)
        $recentFailures = GrowFinanceSyncLogModel::forCompany($companyId)
            ->failed()
            ->where('last_sync_at', '>=', now()->subHour())
            ->count();

        // Get average sync time (if we track it)
        $lastSync = GrowFinanceSyncLogModel::forCompany($companyId)
            ->synced()
            ->latest('last_sync_at')
            ->first();

        return [
            'success_rate' => $stats['success_rate'],
            'recent_failures' => $recentFailures,
            'last_sync' => $lastSync?->last_sync_at,
            'is_healthy' => $stats['success_rate'] >= 95 && $recentFailures < 10,
        ];
    }

    /**
     * Get entity type from model
     */
    private function getEntityType(Model $entity): string
    {
        $class = class_basename($entity);
        
        return match ($class) {
            'InvoiceModel' => 'invoice',
            'ExpenseModel' => 'expense',
            'PaymentModel' => 'payment',
            default => strtolower(str_replace('Model', '', $class)),
        };
    }

    /**
     * Reset sync status for an entity (for retry)
     */
    public function resetSyncStatus(Model $entity): void
    {
        if (property_exists($entity, 'growfinance_synced')) {
            $entity->update([
                'growfinance_synced' => false,
                'growfinance_journal_entry_id' => null,
            ]);
        }

        $log = $this->getLog($entity);
        if ($log) {
            $log->update([
                'sync_status' => 'pending',
                'error_message' => null,
            ]);
        }
    }
}
