<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Customer;
use App\Domain\BizBoost\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel;
use Illuminate\Database\Eloquent\Builder;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        $model = BizBoostCustomerModel::find($id);
        return $model ? Customer::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostCustomerModel::where('business_id', $businessId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Customer $entity): Customer
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostCustomerModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostCustomerModel::create($data);
        return Customer::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostCustomerModel::destroy($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return BizBoostCustomerModel::where('business_id', $businessId)->count();
    }

    public function updatePurchaseStats(int $customerId): void
    {
        $model = BizBoostCustomerModel::find($customerId);
        if ($model) {
            $model->updatePurchaseStats();
        }
    }
}