<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\InventoryAlert;

interface InventoryAlertRepositoryInterface
{
    public function findById(int $id): ?InventoryAlert;

    public function findAllByUser(int $userId, bool $unacknowledgedOnly = false): array;

    public function findByItem(int $itemId): array;

    public function save(InventoryAlert $alert): InventoryAlert;

    public function deleteUnacknowledgedForItem(int $itemId, int $userId): void;

    public function acknowledge(int $id, int $userId): bool;
}
