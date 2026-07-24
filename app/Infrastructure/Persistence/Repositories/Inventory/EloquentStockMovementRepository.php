<?php

namespace App\Infrastructure\Persistence\Repositories\Inventory;

use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\StockMovementModel;

class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function findById(int $id): ?StockMovement
    {
        $model = StockMovementModel::find($id);
        return $model ? StockMovement::reconstitute($model->toArray()) : null;
    }

    public function findByItem(int $itemId, array $filters = []): array
    {
        $query = StockMovementModel::where('item_id', $itemId);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->orderBy('movement_date', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => StockMovement::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findAllByUser(int $userId, array $filters = []): array
    {
        $query = StockMovementModel::where('user_id', $userId)->with('item');

        if (isset($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('movement_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('movement_date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 20;

        return $query->orderBy('movement_date', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => StockMovement::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findRecentByUser(int $userId, int $limit = 10): array
    {
        return StockMovementModel::where('user_id', $userId)
            ->with('item')
            ->orderBy('movement_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => StockMovement::reconstitute($m->toArray()))
            ->toArray();
    }

    public function countRecentByUser(int $userId, int $days = 7): int
    {
        return StockMovementModel::where('user_id', $userId)
            ->where('movement_date', '>=', now()->subDays($days))
            ->count();
    }

    public function findInDateRange(int $userId, string $startDate, ?string $endDate = null): array
    {
        $query = StockMovementModel::where('user_id', $userId)
            ->whereDate('movement_date', '>=', $startDate);

        if ($endDate) {
            $query->whereDate('movement_date', '<=', $endDate);
        }

        return $query->get()
            ->map(fn($m) => StockMovement::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(StockMovement $entity): StockMovement
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            StockMovementModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = StockMovementModel::create($data);
        return StockMovement::reconstitute($model->toArray());
    }
}
