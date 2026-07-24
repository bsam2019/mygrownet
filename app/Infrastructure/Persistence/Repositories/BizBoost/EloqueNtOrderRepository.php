<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Order;
use App\Domain\BizBoost\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostOrderModel;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        $model = BizBoostOrderModel::find($id);
        return $model ? Order::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostOrderModel::where('business_id', $businessId);

        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn($m) => Order::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Order $entity): Order
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostOrderModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostOrderModel::create($data);
        return Order::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostOrderModel::destroy($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return BizBoostOrderModel::where('business_id', $businessId)->count();
    }
}