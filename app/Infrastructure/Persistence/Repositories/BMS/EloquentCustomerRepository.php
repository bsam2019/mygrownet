<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Customer;
use App\Domain\BMS\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer
    {
        $model = CustomerModel::find($id);
        return $model ? Customer::reconstitute($model->toArray()) : null;
    }

    public function save(Customer $entity): Customer
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            CustomerModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = CustomerModel::create($data);
        return Customer::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return CustomerModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return CustomerModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findBySearch(int $companyId, string $search): array
    {
        return CustomerModel::where('company_id', $companyId)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return CustomerModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findWithOutstanding(int $companyId): array
    {
        return CustomerModel::where('company_id', $companyId)->where('outstanding_balance', '>', 0)->get()
            ->map(fn($m) => Customer::reconstitute($m->toArray()))
            ->toArray();
    }
}
