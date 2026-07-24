<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\InventoryItem;

interface InventoryItemRepositoryInterface
{
    public function findById(int $id): ?InventoryItem;

    public function findByIdForUser(int $id, int $userId): ?InventoryItem;

    public function findBySkuOrBarcode(int $userId, string $code): ?InventoryItem;

    public function findAllByUser(int $userId, array $filters = []): array;

    public function findActiveByUser(int $userId): array;

    public function findLowStockByUser(int $userId): array;

    public function save(InventoryItem $item): InventoryItem;

    public function delete(int $id): bool;

    public function countByUser(int $userId): int;

    public function countActiveByUser(int $userId): int;
}
