<?php

namespace App\Domain\Module\Repositories;

use App\Domain\Module\Entities\ModuleSubscription;

interface ModuleSubscriptionRepositoryInterface
{
    /**
     * Find subscription by ID
     * @param string $id Subscription ID
     * @return ModuleSubscription|null
     */
    public function findById(string $id): ?ModuleSubscription;
    
    /**
     * Find subscription by user and module
     * @param int $userId User ID
     * @param string $moduleId Module ID
     * @return ModuleSubscription|null
     */
    public function findByUserAndModule(int $userId, string $moduleId): ?ModuleSubscription;
    
    /**
     * Find all subscriptions for a user
     * @param int $userId User ID
     * @return array<ModuleSubscription>
     */
    public function findByUser(int $userId): array;
    
    /**
     * Find active subscriptions for a user
     * @param int $userId User ID
     * @return array<ModuleSubscription>
     */
    public function findActiveByUser(int $userId): array;
    
    /**
     * Find subscriptions expiring soon
     * @param int $daysAhead Number of days to look ahead
     * @return array<ModuleSubscription>
     */
    public function findExpiring(int $daysAhead = 7): array;
    
    /**
     * Find expired subscriptions
     * @return array<ModuleSubscription>
     */
    public function findExpired(): array;
    
    /**
     * Save subscription
     * @param ModuleSubscription $subscription
     * @return void
     */
    public function save(ModuleSubscription $subscription): void;
    
    /**
     * Delete subscription
     * @param string $id Subscription ID
     * @return void
     */
    public function delete(string $id): void;
}
