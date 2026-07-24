<?php

namespace App\Domain\GrowMart\Repositories;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByUser(int $userId, array $options = []): array;

    public function findAll(array $filters = [], int $perPage = 20): array;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function findItems(int $orderId): array;

    public function addItem(int $orderId, array $data): array;

    public function countByStatus(string $status): int;

    public function sumByStatus(string $status, string $column): int;

    public function revenueOverTime(string $period, int $days): array;

    public function orderStatusBreakdown(): array;

    public function averageOrderValue(): array;

    public function countToday(): int;
}
