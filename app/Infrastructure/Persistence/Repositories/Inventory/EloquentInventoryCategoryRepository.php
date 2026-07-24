<?php

namespace App\Infrastructure\Persistence\Repositories\Inventory;

use App\Domain\Inventory\Entities\InventoryCategory;
use App\Domain\Inventory\Repositories\InventoryCategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryCategoryModel;

class EloquentInventoryCategoryRepository implements InventoryCategoryRepositoryInterface
{
    public function findById(int $id): ?InventoryCategory
    {
        $model = InventoryCategoryModel::find($id);
        return $model ? InventoryCategory::reconstitute($model->toArray()) : null;
    }

    public function findAllByUser(int $userId): array
    {
        return InventoryCategoryModel::where('user_id', $userId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => InventoryCategory::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByName(int $userId, string $name): ?InventoryCategory
    {
        $model = InventoryCategoryModel::where('user_id', $userId)
            ->where('name', $name)
            ->first();
        return $model ? InventoryCategory::reconstitute($model->toArray()) : null;
    }

    public function save(InventoryCategory $entity): InventoryCategory
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InventoryCategoryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InventoryCategoryModel::create($data);
        return InventoryCategory::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return InventoryCategoryModel::where('id', $id)->delete() > 0;
    }
}
