<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Entities\InventoryItem;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Repositories\InventoryRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\InventoryItemId;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventoryService
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function createItem(
        int $userId,
        string $name,
        ?string $sku = null,
        ?string $description = null,
        ?int $categoryId = null,
        string $unit = 'piece',
        float $costPrice = 0,
        float $sellingPrice = 0,
        int $initialStock = 0,
        int $lowStockThreshold = 10,
        ?string $location = null,
        ?string $barcode = null
    ): InventoryItem {
        try {
            $item = InventoryItem::create(
                userId: $userId,
                name: $name,
                sku: $sku,
                description: $description,
                categoryId: $categoryId,
                unit: $unit,
                costPrice: $costPrice,
                sellingPrice: $sellingPrice,
                initialStock: $initialStock,
                lowStockThreshold: $lowStockThreshold,
                location: $location,
                barcode: $barcode
            );

            $savedItem = $this->inventoryRepository->save($item);

            // Record initial stock if any
            if ($initialStock > 0) {
                $this->inventoryRepository->recordStockMovement(
                    itemId: $savedItem->id(),
                    userId: $userId,
                    type: 'initial',
                    quantity: $initialStock,
                    stockBefore: 0,
                    stockAfter: $initialStock,
                    unitCost: $costPrice,
                    notes: 'Initial stock entry'
                );
            }

            Log::info('Inventory item created', [
                'item_id' => $savedItem->id(),
                'user_id' => $userId,
            ]);

            return $savedItem;
        } catch (Throwable $e) {
            Log::error('Failed to create inventory item', [
                'user_id' => $userId,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('create inventory item', $e->getMessage());
        }
    }

    public function updateItem(int $itemId, int $userId, array $data): InventoryItem
    {
        try {
            $item = $this->inventoryRepository->findById(InventoryItemId::fromInt($itemId));
            
            if (!$item || $item->getUserId() !== $userId) {
                throw new \RuntimeException('Item not found or access denied');
            }

            $item->update(
                name: $data['name'] ?? $item->getName(),
                sku: $data['sku'] ?? $item->getSku(),
                description: $data['description'] ?? $item->getDescription(),
                categoryId: $data['category_id'] ?? $item->getCategoryId(),
                unit: $data['unit'] ?? $item->getUnit(),
                costPrice: $data['cost_price'] ?? $item->getCostPrice(),
                sellingPrice: $data['selling_price'] ?? $item->getSellingPrice(),
                lowStockThreshold: $data['low_stock_threshold'] ?? $item->getLowStockThreshold(),
                location: $data['location'] ?? $item->getLocation(),
                barcode: $data['barcode'] ?? $item->getBarcode()
            );

            $savedItem = $this->inventoryRepository->save($item);

            Log::info('Inventory item updated', ['item_id' => $itemId]);

            return $savedItem;
        } catch (Throwable $e) {
            Log::error('Failed to update inventory item', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update inventory item', $e->getMessage());
        }
    }

    public function adjustStock(
        int $itemId,
        int $userId,
        string $type,
        int $quantity,
        ?float $unitCost = null,
        ?string $notes = null
    ): InventoryItem {
        try {
            $item = $this->inventoryRepository->findById(InventoryItemId::fromInt($itemId));
            
            if (!$item || $item->getUserId() !== $userId) {
                throw new \RuntimeException('Item not found or access denied');
            }

            $stockBefore = $item->getCurrentStock();
            
            // Determine if quantity should be positive or negative
            $adjustedQuantity = match ($type) {
                'purchase', 'return', 'adjustment' => abs($quantity),
                'sale', 'damage', 'transfer' => -abs($quantity),
                default => $quantity,
            };

            $item->adjustStock($adjustedQuantity);
            $savedItem = $this->inventoryRepository->save($item);

            // Record the movement
            $this->inventoryRepository->recordStockMovement(
                itemId: $itemId,
                userId: $userId,
                type: $type,
                quantity: $adjustedQuantity,
                stockBefore: $stockBefore,
                stockAfter: $savedItem->getCurrentStock(),
                unitCost: $unitCost ?? $item->getCostPrice(),
                notes: $notes
            );

            Log::info('Stock adjusted', [
                'item_id' => $itemId,
                'type' => $type,
                'quantity' => $adjustedQuantity,
                'stock_after' => $savedItem->getCurrentStock(),
            ]);

            return $savedItem;
        } catch (Throwable $e) {
            Log::error('Failed to adjust stock', [
                'item_id' => $itemId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('adjust stock', $e->getMessage());
        }
    }

    public function deleteItem(int $itemId, int $userId): void
    {
        try {
            $item = $this->inventoryRepository->findById(InventoryItemId::fromInt($itemId));
            
            if (!$item || $item->getUserId() !== $userId) {
                throw new \RuntimeException('Item not found or access denied');
            }

            $this->inventoryRepository->delete(InventoryItemId::fromInt($itemId));

            Log::info('Inventory item deleted', ['item_id' => $itemId]);
        } catch (Throwable $e) {
            Log::error('Failed to delete inventory item', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('delete inventory item', $e->getMessage());
        }
    }

    public function getItemById(int $itemId, int $userId): ?InventoryItem
    {
        $item = $this->inventoryRepository->findById(InventoryItemId::fromInt($itemId));
        
        if (!$item || $item->getUserId() !== $userId) {
            return null;
        }

        return $item;
    }

    public function getItemsForUser(int $userId, array $filters = []): array
    {
        return $this->inventoryRepository->findByUserIdWithFilters($userId, $filters);
    }

    public function getLowStockItems(int $userId): array
    {
        return $this->inventoryRepository->findLowStockItems($userId);
    }

    public function getOutOfStockItems(int $userId): array
    {
        return $this->inventoryRepository->findOutOfStockItems($userId);
    }

    public function searchItems(int $userId, string $query): array
    {
        return $this->inventoryRepository->searchItems($userId, $query);
    }

    public function getStatistics(int $userId): array
    {
        return $this->inventoryRepository->getStatistics($userId);
    }

    public function getCategories(int $userId): array
    {
        return $this->inventoryRepository->getCategories($userId);
    }

    public function createCategory(int $userId, string $name, ?string $description = null, ?string $color = null): array
    {
        return $this->inventoryRepository->createCategory($userId, $name, $description, $color);
    }

    public function updateCategory(int $categoryId, string $name, ?string $description = null, ?string $color = null): array
    {
        return $this->inventoryRepository->updateCategory($categoryId, $name, $description, $color);
    }

    public function deleteCategory(int $categoryId): void
    {
        $this->inventoryRepository->deleteCategory($categoryId);
    }

    public function getStockMovements(int $itemId, int $limit = 50): array
    {
        return $this->inventoryRepository->getStockMovements($itemId, $limit);
    }

    public function getRecentMovements(int $userId, int $limit = 20): array
    {
        return $this->inventoryRepository->getRecentMovements($userId, $limit);
    }
}
