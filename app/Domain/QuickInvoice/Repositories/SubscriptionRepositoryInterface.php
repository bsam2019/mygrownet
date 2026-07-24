<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

interface SubscriptionRepositoryInterface
{
    public function findById(string $id): ?array;

    public function getCurrentSubscription(int $userId): ?array;

    public function getOrCreateFreeSubscription(int $userId): array;

    public function incrementUsage(string $id): void;

    public function findActiveByUser(int $userId): ?array;

    public function findAllActive(): array;

    public function countActive(): int;

    public function countDistinctUsers(): int;

    public function create(array $data): array;

    public function deactivateByUser(int $userId): void;

    public function sumLastPaymentAmount(): float;

    public function paginateWithFilters(?string $search, ?string $tierFilter, int $perPage = 20): array;
}