<?php

namespace App\Infrastructure\BizDocs\Persistence\Repositories;

use App\Domain\BizDocs\CustomerManagement\Entities\Customer;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;
use App\Infrastructure\BizDocs\Persistence\Eloquent\CustomerModel;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function save(Customer $customer): Customer
    {
        $data = [
            'business_id' => $customer->businessId(),
            'name' => $customer->name(),
            'address' => $customer->address(),
            'phone' => $customer->phone(),
            'email' => $customer->email(),
            'tpin' => $customer->tpin(),
            'notes' => $customer->notes(),
        ];

        if ($customer->id()) {
            $model = CustomerModel::findOrFail($customer->id());
            $model->update($data);
        } else {
            $model = CustomerModel::create($data);
        }

        return $this->findById($model->id);
    }

    public function findById(int $id): ?Customer
    {
        $model = CustomerModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByBusiness(int $businessId, int $page = 1, int $perPage = 20): array
    {
        $models = CustomerModel::where('business_id', $businessId)
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findByPhone(int $businessId, string $phone): ?Customer
    {
        $model = CustomerModel::where('business_id', $businessId)
            ->where('phone', $phone)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findByEmail(int $businessId, string $email): ?Customer
    {
        $model = CustomerModel::where('business_id', $businessId)
            ->where('email', $email)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function search(int $businessId, string $query, int $page = 1, int $perPage = 20): array
    {
        $models = CustomerModel::where('business_id', $businessId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(int $id): bool
    {
        return CustomerModel::where('id', $id)->delete() > 0;
    }

    public function countByBusiness(int $businessId): int
    {
        return CustomerModel::where('business_id', $businessId)->count();
    }

    private function toDomainEntity(CustomerModel $model): Customer
    {
        return Customer::fromPersistence(
            $model->id,
            $model->business_id,
            $model->name,
            $model->address,
            $model->phone,
            $model->email,
            $model->tpin,
            $model->notes
        );
    }
}
