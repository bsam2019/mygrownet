<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\InventoryItem;
use App\Domain\BMS\Repositories\InventoryItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\InventoryItemModel;

class EloquentInventoryItemRepository implements InventoryItemRepositoryInterface
{
    public function findById(int $id): ?InventoryItem
    {
        $model = InventoryItemModel::find($id);
        return $model ? InventoryItem::reconstitute($model->toArray()) : null;
    }

    public function save(InventoryItem $entity): InventoryItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InventoryItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InventoryItemModel::create($data);
        return InventoryItem::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return InventoryItemModel::where('company_id', $companyId)->get()
            ->map(fn($m) => InventoryItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findLowStock(int $companyId): array
    {
        return InventoryItemModel::where('company_id', $companyId)
            ->whereNotNull('reorder_level')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->get()
            ->map(fn($m) => InventoryItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCategory(int $categoryId): array
    {
        return InventoryItemModel::where('category_id', $categoryId)->get()
            ->map(fn($m) => InventoryItem::reconstitute($m->toArray()))
            ->toArray();
    }
}
