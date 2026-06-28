<?php

namespace App\Domain\Module\Repositories;

/**
 * Module Usage Repository Interface
 * 
 * Defines the contract for tracking module usage (freemium limits).
 */
interface ModuleUsageRepositoryInterface
{
    /**
     * Get current usage for a specific type
     */
    public function getUsage(int $userId, string $moduleId, string $usageType): int;

    /**
     * Get all usage for a user and module in current period
     */
    public function getAllUsage(int $userId, string $moduleId): array;

    /**
     * Increment usage count
     */
    public function incrementUsage(int $userId, string $moduleId, string $usageType, int $amount = 1): int;

    /**
     * Check if user is within usage limit
     */
    public function isWithinLimit(int $userId, string $moduleId, string $usageType, int $limit): bool;

    /**
     * Get remaining usage for a type
     */
    public function getRemainingUsage(int $userId, string $moduleId, string $usageType, int $limit): int;

    /**
     * Reset usage for a new period
     */
    public function resetUsage(int $userId, string $moduleId, string $usageType): void;

    /**
     * Get usage history for a user and module
     */
    public function getUsageHistory(int $userId, string $moduleId, int $months = 6): array;
}
