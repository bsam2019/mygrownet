<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\InventoryCategory;

interface InventoryCategoryRepositoryInterface
{
    public function findById(int $id): ?InventoryCategory;

    public function findAllByUser(int $userId): array;

    public function findByName(int $userId, string $name): ?InventoryCategory;

    public function save(InventoryCategory $category): InventoryCategory;

    public function delete(int $id): bool;
}
