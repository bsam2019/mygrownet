<?php

namespace App\Infrastructure\Persistence\Repositories\Inventory;

use App\Domain\Inventory\Entities\InventoryItem;
use App\Domain\Inventory\Repositories\InventoryItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryItemModel;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentInventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function findById(int $id): ?InventoryItem
    {
        $model = InventoryItemModel::find($id);
        return $model ? InventoryItem::reconstitute($model->toArray()) : null;
    }

    public function findByIdForUser(int $id, int $userId): ?InventoryItem
    {
        $model = InventoryItemModel::where('id', $id)->where('user_id', $userId)->first();
        return $model ? InventoryItem::reconstitute($model->toArray()) : null;
    }

    public function findBySkuOrBarcode(int $userId, string $code): ?InventoryItem
    {
        $model = InventoryItemModel::where('user_id', $userId)
            ->where(function ($q) use ($code) {
                $q->where('sku', $code)->orWhere('barcode', $code);
            })
            ->first();
        return $model ? InventoryItem::reconstitute($model->toArray()) : null;
    }

    public function findAllByUser(int $userId, array $filters = []): array
    {
        $query = InventoryItemModel::where('user_id', $userId)->with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereColumn('current_stock', '<=', 'low_stock_threshold');
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        $perPage = $filters['per_page'] ?? null;
        $results = $perPage ? $query->paginate($perPage) : $query->get();

        $items = $results instanceof LengthAwarePaginator
            ? $results->items()
            : $results->all();

        $mapped = array_map(
            fn($m) => InventoryItem::reconstitute($m->toArray()),
            $items
        );

        if ($results instanceof LengthAwarePaginator) {
            $results->setCollection(collect($mapped));
            return $results->toArray();
        }

        return $mapped;
    }

    public function findActiveByUser(int $userId): array
    {
        return InventoryItemModel::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(fn($m) => InventoryItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findLowStockByUser(int $userId): array
    {
        return InventoryItemModel::where('user_id', $userId)
            ->where('is_active', true)
            ->where('track_stock', true)
            ->whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->orderBy('current_stock')
            ->get()
            ->map(fn($m) => InventoryItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(InventoryItem $entity): InventoryItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        if ($id) {
            InventoryItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InventoryItemModel::create($data);
        return InventoryItem::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return InventoryItemModel::where('id', $id)->delete() > 0;
    }

    public function countByUser(int $userId): int
    {
        return InventoryItemModel::where('user_id', $userId)->count();
    }

    public function countActiveByUser(int $userId): int
    {
        return InventoryItemModel::where('user_id', $userId)->where('is_active', true)->count();
    }
}
