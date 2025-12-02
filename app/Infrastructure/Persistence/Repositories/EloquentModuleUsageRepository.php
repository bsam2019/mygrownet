<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Module\Repositories\ModuleUsageRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ModuleUsageModel;

/**
 * Eloquent Module Usage Repository
 * 
 * Implementation of usage tracking for freemium modules.
 */
class EloquentModuleUsageRepository implements ModuleUsageRepositoryInterface
{
    /**
     * Get current usage for a specific type
     */
    public function getUsage(int $userId, string $moduleId, string $usageType): int
    {
        $usage = ModuleUsageModel::forUser($userId)
            ->forModule($moduleId)
            ->ofType($usageType)
            ->currentPeriod()
            ->first();

        return $usage?->count ?? 0;
    }

    /**
     * Get all usage for a user and module in current period
     */
    public function getAllUsage(int $userId, string $moduleId): array
    {
        $usages = ModuleUsageModel::forUser($userId)
            ->forModule($moduleId)
            ->currentPeriod()
            ->get();

        $result = [];
        foreach ($usages as $usage) {
            $result[$usage->usage_type] = $usage->count;
        }

        return $result;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(int $userId, string $moduleId, string $usageType, int $amount = 1): int
    {
        $usage = ModuleUsageModel::getOrCreateForCurrentPeriod($userId, $moduleId, $usageType);
        $usage->incrementUsage($amount);

        return $usage->count;
    }

    /**
     * Check if user is within usage limit
     */
    public function isWithinLimit(int $userId, string $moduleId, string $usageType, int $limit): bool
    {
        if ($limit === -1) {
            return true; // Unlimited
        }

        $currentUsage = $this->getUsage($userId, $moduleId, $usageType);
        return $currentUsage < $limit;
    }

    /**
     * Get remaining usage for a type
     */
    public function getRemainingUsage(int $userId, string $moduleId, string $usageType, int $limit): int
    {
        if ($limit === -1) {
            return PHP_INT_MAX; // Unlimited
        }

        $currentUsage = $this->getUsage($userId, $moduleId, $usageType);
        return max(0, $limit - $currentUsage);
    }

    /**
     * Reset usage for a new period
     */
    public function resetUsage(int $userId, string $moduleId, string $usageType): void
    {
        // Create a new record for the current period with 0 count
        // Old records are kept for history
        ModuleUsageModel::getOrCreateForCurrentPeriod($userId, $moduleId, $usageType);
    }

    /**
     * Get usage history for a user and module
     */
    public function getUsageHistory(int $userId, string $moduleId, int $months = 6): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        $usages = ModuleUsageModel::forUser($userId)
            ->forModule($moduleId)
            ->where('period_start', '>=', $startDate)
            ->orderBy('period_start', 'desc')
            ->get();

        $history = [];
        foreach ($usages as $usage) {
            $period = $usage->period_start->format('Y-m');
            if (!isset($history[$period])) {
                $history[$period] = [];
            }
            $history[$period][$usage->usage_type] = $usage->count;
        }

        return $history;
    }
}
