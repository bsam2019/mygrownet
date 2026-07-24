<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Customer;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceCustomerModel;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        $model = GrowFinanceCustomerModel::find($id);
        return $model ? Customer::reconstitute($model->toArray()) : null;
    }

    public function save(Customer $entity): Customer
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceCustomerModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceCustomerModel::create($data);
        return Customer::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceCustomerModel::forBusiness($businessId)->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceCustomerModel::forBusiness($businessId)->active()->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findWithOutstanding(int $businessId): array
    {
        return GrowFinanceCustomerModel::forBusiness($businessId)->where('outstanding_balance', '>', 0)->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }
}
