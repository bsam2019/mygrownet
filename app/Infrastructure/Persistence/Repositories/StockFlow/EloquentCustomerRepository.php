<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Customer;
use App\Domain\StockFlow\Repositories\CustomerRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CustomerId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCustomerModel;
use DateTimeImmutable;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(CustomerId $id): ?Customer
    {
        $model = SaCustomerModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaCustomerModel::where('sa_company_id', $companyId->toInt())
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByName(CompanyId $companyId, string $name): array
    {
        return SaCustomerModel::where('sa_company_id', $companyId->toInt())
            ->where('name', 'like', "%{$name}%")
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Customer $customer): Customer
    {
        $data = $customer->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($customer->id() === 0) {
            $model = SaCustomerModel::create($data);
        } else {
            $model = SaCustomerModel::find($customer->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(CustomerId $id): void
    {
        SaCustomerModel::destroy($id->toInt());
    }

    public function countByCompanyId(CompanyId $companyId): int
    {
        return SaCustomerModel::where('sa_company_id', $companyId->toInt())->count();
    }

    private function toDomainEntity(SaCustomerModel $model): Customer
    {
        return Customer::reconstitute(
            id: CustomerId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            city: $model->city,
            country: $model->country,
            creditLimit: $model->credit_limit !== null ? Money::fromFloat((float) $model->credit_limit) : null,
            paymentTerms: $model->payment_terms,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
