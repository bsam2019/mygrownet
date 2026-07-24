<?php

namespace App\Domain\Storage\Repositories;

interface StorageSubscriptionRepositoryInterface
{
    public function getActivePlans(): array;

    public function findPlanById(string $planId): ?array;

    public function findFreePlan(): ?array;

    public function getActiveSubscription(int $userId): ?array;

    public function createOrUpdateSubscription(int $userId, string $planId, string $billingCycle = 'monthly', string $status = 'active'): void;

    public function getOrCreateUsage(int $userId): array;

    public function updateUsage(int $userId, int $usedBytes, int $filesCount): void;
}