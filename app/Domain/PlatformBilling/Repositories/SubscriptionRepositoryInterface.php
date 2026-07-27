<?php

namespace App\Domain\PlatformBilling\Repositories;

use App\Domain\PlatformBilling\Entities\Subscription;

interface SubscriptionRepositoryInterface
{
    public function findById(int $id): ?Subscription;
    public function findByUser(int $userId): array;
    public function findByStatus(string $status): array;
    public function findOverdue(): array;
    public function findExpiring(int $withinDays): array;
    public function save(Subscription $subscription): Subscription;
    public function delete(int $id): void;
}
