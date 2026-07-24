<?php

namespace App\Domain\GrowMart\Repositories;

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findAll(array $filters = []): array;

    public function findActive(): array;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function inventoryCount(int $warehouseId): int;

    public function countAll(): int;
}
