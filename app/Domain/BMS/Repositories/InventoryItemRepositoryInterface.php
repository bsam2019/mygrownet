<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\InventoryItem;

interface InventoryItemRepositoryInterface
{
    public function findById(int $id): ?InventoryItem;

    public function save(InventoryItem $item): InventoryItem;

    public function findByCompany(int $companyId): array;

    public function findLowStock(int $companyId): array;

    public function findByCategory(int $categoryId): array;
}
