<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\InventoryAlert;
use App\Domain\Inventory\Entities\InventoryCategory;
use App\Domain\Inventory\Entities\InventoryItem;
use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Repositories\InventoryAlertRepositoryInterface;
use App\Domain\Inventory\Repositories\InventoryCategoryRepositoryInterface;
use App\Domain\Inventory\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    protected string $moduleContext = 'inventory';
    protected ?int $userId = null;

    public function __construct(
        private InventoryCategoryRepositoryInterface $categoryRepository,
        private InventoryItemRepositoryInterface $itemRepository,
        private StockMovementRepositoryInterface $movementRepository,
        private InventoryAlertRepositoryInterface $alertRepository,
    ) {}

    public function forModule(string $module): self
    {
        $this->moduleContext = $module;
        return $this;
    }

    public function forUser(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    protected function getUserId(): int
    {
        return $this->userId ?? auth()->id();
    }

    public function getCategories(): array
    {
        $categories = $this->categoryRepository->findAllByUser($this->getUserId());
        return array_map(fn(InventoryCategory $c) => [
            ...$c->toArray(),
            'items_count' => 0,
        ], $categories);
    }

    public function createCategory(array $data): InventoryCategory
    {
        $category = InventoryCategory::reconstitute([
            'user_id' => $this->getUserId(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#6b7280',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return $this->categoryRepository->save($category);
    }

    public function updateCategory(int $categoryId, array $data): InventoryCategory
    {
        $category = $this->categoryRepository->findById($categoryId);

        if (!$category) {
            throw new \RuntimeException('Category not found');
        }

        $updated = InventoryCategory::reconstitute([
            ...$category->toArray(),
            ...$data,
        ]);

        return $this->categoryRepository->save($updated);
    }

    public function deleteCategory(int $categoryId): bool
    {
        return $this->categoryRepository->delete($categoryId);
    }

    public function getItems(array $filters = [])
    {
        return $this->itemRepository->findAllByUser($this->getUserId(), $filters);
    }

    public function getItem(int $itemId): ?InventoryItem
    {
        return $this->itemRepository->findByIdForUser($itemId, $this->getUserId());
    }

    public function findBySkuOrBarcode(string $code): ?InventoryItem
    {
        return $this->itemRepository->findBySkuOrBarcode($this->getUserId(), $code);
    }

    public function createItem(array $data): InventoryItem
    {
        $item = InventoryItem::reconstitute([
            'user_id' => $this->getUserId(),
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'unit' => $data['unit'] ?? 'piece',
            'cost_price' => $data['cost_price'] ?? 0,
            'selling_price' => $data['selling_price'] ?? 0,
            'current_stock' => $data['current_stock'] ?? 0,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? 10,
            'location' => $data['location'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'track_stock' => $data['track_stock'] ?? true,
        ]);

        $saved = $this->itemRepository->save($item);

        if ($saved->currentStock > 0) {
            $this->recordMovement(
                $saved->id,
                'initial',
                $saved->currentStock,
                'Initial stock',
                $saved->costPrice,
                null
            );
        }

        return $saved;
    }

    public function updateItem(int $itemId, array $data): InventoryItem
    {
        $item = $this->itemRepository->findByIdForUser($itemId, $this->getUserId());

        if (!$item) {
            throw new \RuntimeException('Item not found');
        }

        $updated = InventoryItem::reconstitute([
            ...$item->toArray(),
            ...$data,
        ]);

        return $this->itemRepository->save($updated);
    }

    public function deleteItem(int $itemId): bool
    {
        return $this->itemRepository->delete($itemId);
    }

    public function adjustStock(int $itemId, int $quantity, string $type, string $notes = null, $reference = null): InventoryItem
    {
        $item = $this->itemRepository->findByIdForUser($itemId, $this->getUserId());

        if (!$item) {
            throw new \RuntimeException('Item not found');
        }

        if (!$item->trackStock) {
            return $item;
        }

        return DB::transaction(function () use ($item, $quantity, $type, $notes, $reference) {
            $stockBefore = $item->currentStock;
            $stockAfter = $stockBefore + $quantity;

            $updated = InventoryItem::reconstitute([
                ...$item->toArray(),
                'current_stock' => $stockAfter,
            ]);

            $saved = $this->itemRepository->save($updated);

            $this->recordMovement(
                $saved->id,
                $type,
                $quantity,
                $notes,
                $saved->costPrice,
                $reference,
                $stockBefore,
                $stockAfter
            );

            $this->checkStockAlerts($saved);

            return $saved;
        });
    }

    public function addStock(int $itemId, int $quantity, string $type = 'purchase', string $notes = null): InventoryItem
    {
        return $this->adjustStock($itemId, abs($quantity), $type, $notes);
    }

    public function removeStock(int $itemId, int $quantity, string $type = 'sale', string $notes = null, $reference = null): InventoryItem
    {
        return $this->adjustStock($itemId, -abs($quantity), $type, $notes, $reference);
    }

    protected function recordMovement(
        int $itemId,
        string $type,
        int $quantity,
        string $notes = null,
        float $unitCost = null,
        $reference = null,
        int $stockBefore = null,
        int $stockAfter = null,
    ): StockMovement {
        $movement = StockMovement::reconstitute([
            'item_id' => $itemId,
            'user_id' => $this->getUserId(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore ?? 0,
            'stock_after' => $stockAfter ?? 0,
            'unit_cost' => $unitCost,
            'total_value' => $unitCost ? abs($quantity) * $unitCost : null,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'notes' => $notes,
            'movement_date' => now()->toDateTimeString(),
        ]);

        return $this->movementRepository->save($movement);
    }

    public function getStockMovements(int $itemId, array $filters = [])
    {
        return $this->movementRepository->findByItem($itemId, $filters);
    }

    public function getRecentMovements(int $limit = 10): array
    {
        return $this->movementRepository->findRecentByUser($this->getUserId(), $limit);
    }

    public function getAllMovements(array $filters = [])
    {
        return $this->movementRepository->findAllByUser($this->getUserId(), $filters);
    }

    protected function checkStockAlerts(InventoryItem $item): void
    {
        $userId = $this->getUserId();

        $this->alertRepository->deleteUnacknowledgedForItem($item->id, $userId);

        if ($item->currentStock <= 0) {
            $alert = InventoryAlert::reconstitute([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'out_of_stock',
                'threshold_value' => 0,
                'current_value' => $item->currentStock,
            ]);
            $this->alertRepository->save($alert);
        } elseif ($item->isLowStock()) {
            $alert = InventoryAlert::reconstitute([
                'item_id' => $item->id,
                'user_id' => $userId,
                'type' => 'low_stock',
                'threshold_value' => $item->lowStockThreshold,
                'current_value' => $item->currentStock,
            ]);
            $this->alertRepository->save($alert);
        }
    }

    public function getAlerts(bool $unacknowledgedOnly = true): array
    {
        return $this->alertRepository->findAllByUser($this->getUserId(), $unacknowledgedOnly);
    }

    public function acknowledgeAlert(int $alertId): bool
    {
        return $this->alertRepository->acknowledge($alertId, $this->getUserId());
    }

    public function getSummary(): array
    {
        $userId = $this->getUserId();
        $items = $this->itemRepository->findAllByUser($userId);
        $recentMovementsCount = $this->movementRepository->countRecentByUser($userId, 7);

        $totalItems = count($items);
        $activeItems = 0;
        $totalStockValue = 0;
        $totalRetailValue = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($items as $item) {
            if ($item->isActive) {
                $activeItems++;
            }
            $totalStockValue += $item->stockValue();
            $totalRetailValue += $item->retailValue();
            if ($item->isLowStock()) {
                $lowStockCount++;
            }
            if ($item->isOutOfStock()) {
                $outOfStockCount++;
            }
        }

        $categories = $this->categoryRepository->findAllByUser($userId);

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'total_stock_value' => $totalStockValue,
            'total_retail_value' => $totalRetailValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'categories_count' => count($categories),
            'recent_movements_count' => $recentMovementsCount,
        ];
    }

    public function getLowStockItems(): array
    {
        return $this->itemRepository->findLowStockByUser($this->getUserId());
    }
}
