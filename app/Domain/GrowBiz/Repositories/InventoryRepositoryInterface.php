<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Repositories;

use App\Domain\GrowBiz\Entities\InventoryItem;
use App\Domain\GrowBiz\ValueObjects\InventoryItemId;

interface InventoryRepositoryInterface
{
    public function findById(InventoryItemId $id): ?InventoryItem;
    
    public function findByUserId(int $userId): array;
    
    public function findByUserIdWithFilters(int $userId, array $filters): array;
    
    public function findLowStockItems(int $userId): array;
    
    public function findOutOfStockItems(int $userId): array;
    
    public function findByCategory(int $userId, int $categoryId): array;
    
    public function findBySku(int $userId, string $sku): ?InventoryItem;
    
    public function findByBarcode(int $userId, string $barcode): ?InventoryItem;
    
    public function searchItems(int $userId, string $query): array;
    
    public function getCategories(int $userId): array;
    
    public function createCategory(int $userId, string $name, ?string $description = null, ?string $color = null): array;
    
    public function updateCategory(int $categoryId, string $name, ?string $description = null, ?string $color = null): array;
    
    public function deleteCategory(int $categoryId): void;
    
    public function getStatistics(int $userId): array;
    
    public function save(InventoryItem $item): InventoryItem;
    
    public function delete(InventoryItemId $id): void;
    
    public function recordStockMovement(
        int $itemId,
        int $userId,
        string $type,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?float $unitCost = null,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void;
    
    public function getStockMovements(int $itemId, int $limit = 50): array;
    
    public function getRecentMovements(int $userId, int $limit = 20): array;
}
