<?php

namespace App\Domain\Module\Contracts;

use App\Models\User;

/**
 * Module Usage Provider Interface
 * 
 * Each module implements this interface to provide its usage metrics.
 * This allows the centralized subscription system to check limits
 * without knowing the specifics of each module's data structure.
 */
interface ModuleUsageProviderInterface
{
    /**
     * Get the module ID this provider handles
     */
    public function getModuleId(): string;

    /**
     * Get all current usage metrics for a user
     * 
     * Returns an associative array of metric_key => current_value
     * Example: ['transactions_per_month' => 45, 'customers' => 12]
     */
    public function getUsageMetrics(User $user): array;

    /**
     * Get a specific usage metric value
     */
    public function getMetric(User $user, string $metricKey): int;

    /**
     * Clear cached usage data for a user
     * Called after creating/deleting resources
     */
    public function clearCache(User $user): void;

    /**
     * Get storage usage in bytes (if applicable)
     */
    public function getStorageUsed(User $user): int;
}
