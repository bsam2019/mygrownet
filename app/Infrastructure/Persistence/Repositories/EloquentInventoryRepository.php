<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\GrowBiz\Entities\InventoryItem;
use App\Domain\GrowBiz\Repositories\InventoryRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\InventoryItemId;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\InventoryCategoryModel;
use App\Infrastructure\Persistence\Eloquent\StockMovementModel;
use DateTimeImmutable;

class EloquentInventoryRepository implements InventoryRepositoryInterface
{
    public function findById(InventoryItemId $id): ?InventoryItem
    {
        $model = InventoryItemModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        $models = InventoryItemModel::forUser($userId)
            ->active()
            ->orderBy('name')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByUserIdWithFilters(int $userId, array $filters): array
    {
        $query = InventoryItemModel::forUser($userId);

        if (!isset($filters['include_inactive']) || !$filters['include_inactive']) {
            $query->active();
        }

        if (!empty($filters['category_id'])) {
            $query->inCategory($filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['stock_status'])) {
            match ($filters['stock_status']) {
                'low' => $query->lowStock(),
                'out' => $query->outOfStock(),
                default => null,
            };
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        $models = $query->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findLowStockItems(int $userId): array
    {
        $models = InventoryItemModel::forUser($userId)
            ->active()
            ->lowStock()
            ->orderBy('current_stock')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findOutOfStockItems(int $userId): array
    {
        $models = InventoryItemModel::forUser($userId)
            ->active()
            ->outOfStock()
            ->orderBy('name')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByCategory(int $userId, int $categoryId): array
    {
        $models = InventoryItemModel::forUser($userId)
            ->active()
            ->inCategory($categoryId)
            ->orderBy('name')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findBySku(int $userId, string $sku): ?InventoryItem
    {
        $model = InventoryItemModel::forUser($userId)
            ->where('sku', $sku)
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByBarcode(int $userId, string $barcode): ?InventoryItem
    {
        $model = InventoryItemModel::forUser($userId)
            ->where('barcode', $barcode)
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function searchItems(int $userId, string $query): array
    {
        $models = InventoryItemModel::forUser($userId)
            ->active()
            ->search($query)
            ->limit(20)
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function getCategories(int $userId): array
    {
        return InventoryCategoryModel::forUser($userId)
            ->withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'description' => $c->description,
                'color' => $c->color,
                'items_count' => $c->items_count,
            ])
            ->all();
    }

    public function createCategory(int $userId, string $name, ?string $description = null, ?string $color = null): array
    {
        $category = InventoryCategoryModel::create([
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'color' => $color ?? '#6b7280',
        ]);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'color' => $category->color,
            'items_count' => 0,
        ];
    }

    public function updateCategory(int $categoryId, string $name, ?string $description = null, ?string $color = null): array
    {
        $category = InventoryCategoryModel::findOrFail($categoryId);
        $category->update([
            'name' => $name,
            'description' => $description,
            'color' => $color ?? $category->color,
        ]);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'color' => $category->color,
            'items_count' => $category->items()->count(),
        ];
    }

    public function deleteCategory(int $categoryId): void
    {
        InventoryCategoryModel::destroy($categoryId);
    }

    public function getStatistics(int $userId): array
    {
        $items = InventoryItemModel::forUser($userId)->active()->get();
        
        $totalItems = $items->count();
        $totalStock = $items->sum('current_stock');
        $totalValue = $items->sum(fn($i) => $i->current_stock * $i->cost_price);
        $potentialRevenue = $items->sum(fn($i) => $i->current_stock * $i->selling_price);
        $lowStockCount = $items->filter(fn($i) => $i->track_stock && $i->current_stock <= $i->low_stock_threshold)->count();
        $outOfStockCount = $items->filter(fn($i) => $i->track_stock && $i->current_stock <= 0)->count();

        return [
            'total_items' => $totalItems,
            'total_stock' => $totalStock,
            'total_value' => round($totalValue, 2),
            'potential_revenue' => round($potentialRevenue, 2),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'categories_count' => InventoryCategoryModel::forUser($userId)->count(),
        ];
    }

    public function save(InventoryItem $item): InventoryItem
    {
        $data = [
            'user_id' => $item->getUserId(),
            'name' => $item->getName(),
            'sku' => $item->getSku(),
            'description' => $item->getDescription(),
            'category_id' => $item->getCategoryId(),
            'unit' => $item->getUnit(),
            'cost_price' => $item->getCostPrice(),
            'selling_price' => $item->getSellingPrice(),
            'current_stock' => $item->getCurrentStock(),
            'low_stock_threshold' => $item->getLowStockThreshold(),
            'location' => $item->getLocation(),
            'barcode' => $item->getBarcode(),
            'image_path' => $item->getImagePath(),
            'is_active' => $item->isActive(),
            'track_stock' => $item->tracksStock(),
        ];

        if ($item->id() === 0) {
            $model = InventoryItemModel::create($data);
        } else {
            $model = InventoryItemModel::find($item->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(InventoryItemId $id): void
    {
        InventoryItemModel::destroy($id->toInt());
    }

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
    ): void {
        StockMovementModel::create([
            'item_id' => $itemId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'unit_cost' => $unitCost,
            'total_value' => $unitCost ? abs($quantity) * $unitCost : null,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'movement_date' => now(),
        ]);
    }

    public function getStockMovements(int $itemId, int $limit = 50): array
    {
        return StockMovementModel::forItem($itemId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'type' => $m->type,
                'quantity' => $m->quantity,
                'stock_before' => $m->stock_before,
                'stock_after' => $m->stock_after,
                'unit_cost' => $m->unit_cost,
                'total_value' => $m->total_value,
                'notes' => $m->notes,
                'user_name' => $m->user?->name,
                'movement_date' => $m->movement_date->format('Y-m-d H:i:s'),
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
            ])
            ->all();
    }

    public function getRecentMovements(int $userId, int $limit = 20): array
    {
        return StockMovementModel::forUser($userId)
            ->with(['item:id,name,sku', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'item_id' => $m->item_id,
                'item_name' => $m->item?->name,
                'item_sku' => $m->item?->sku,
                'type' => $m->type,
                'quantity' => $m->quantity,
                'stock_after' => $m->stock_after,
                'notes' => $m->notes,
                'user_name' => $m->user?->name,
                'created_at' => $m->created_at->format('Y-m-d H:i:s'),
            ])
            ->all();
    }

    private function toDomainEntity(InventoryItemModel $model): InventoryItem
    {
        return InventoryItem::reconstitute(
            id: InventoryItemId::fromInt($model->id),
            userId: $model->user_id,
            name: $model->name,
            sku: $model->sku,
            description: $model->description,
            categoryId: $model->category_id,
            unit: $model->unit,
            costPrice: (float) $model->cost_price,
            sellingPrice: (float) $model->selling_price,
            currentStock: $model->current_stock,
            lowStockThreshold: $model->low_stock_threshold,
            location: $model->location,
            barcode: $model->barcode,
            imagePath: $model->image_path,
            isActive: $model->is_active,
            trackStock: $model->track_stock,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
