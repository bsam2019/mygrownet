<?php

namespace App\Infrastructure\Persistence\Repositories\Inventory;

use App\Domain\Inventory\Entities\InventoryAlert;
use App\Domain\Inventory\Repositories\InventoryAlertRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\InventoryAlertModel;

class EloquentInventoryAlertRepository implements InventoryAlertRepositoryInterface
{
    public function findById(int $id): ?InventoryAlert
    {
        $model = InventoryAlertModel::find($id);
        return $model ? InventoryAlert::reconstitute($model->toArray()) : null;
    }

    public function findAllByUser(int $userId, bool $unacknowledgedOnly = false): array
    {
        $query = InventoryAlertModel::where('user_id', $userId)->with('item');

        if ($unacknowledgedOnly) {
            $query->where('is_acknowledged', false);
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => InventoryAlert::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByItem(int $itemId): array
    {
        return InventoryAlertModel::where('item_id', $itemId)
            ->get()
            ->map(fn($m) => InventoryAlert::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(InventoryAlert $entity): InventoryAlert
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InventoryAlertModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InventoryAlertModel::create($data);
        return InventoryAlert::reconstitute($model->toArray());
    }

    public function deleteUnacknowledgedForItem(int $itemId, int $userId): void
    {
        InventoryAlertModel::where('item_id', $itemId)
            ->where('user_id', $userId)
            ->where('is_acknowledged', false)
            ->delete();
    }

    public function acknowledge(int $id, int $userId): bool
    {
        return InventoryAlertModel::where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'is_acknowledged' => true,
                'acknowledged_at' => now(),
            ]) > 0;
    }
}
