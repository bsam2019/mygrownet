<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use App\Models\GrowMart\GrowMartWarehouse;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartWarehouse::find($id);
        return $model?->toArray();
    }

    public function findAll(array $filters = []): array
    {
        $query = GrowMartWarehouse::withCount('inventory');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->toArray();
    }

    public function findActive(): array
    {
        return GrowMartWarehouse::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function save(array $data): array
    {
        $model = GrowMartWarehouse::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartWarehouse::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartWarehouse::destroy($id) > 0;
    }

    public function inventoryCount(int $warehouseId): int
    {
        return GrowMartWarehouse::find($warehouseId)?->inventory()->count() ?? 0;
    }

    public function countAll(): int
    {
        return GrowMartWarehouse::count();
    }
}
