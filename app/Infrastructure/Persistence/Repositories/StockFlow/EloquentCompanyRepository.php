<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Company;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel;
use DateTimeImmutable;

class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    public function findById(CompanyId $id): ?Company
    {
        $model = SaCompanyModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActive(): array
    {
        return SaCompanyModel::where('status', 'active')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return SaCompanyModel::withCount(['departments', 'bins', 'items', 'audits'])
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findBySubdomain(string $subdomain): ?Company
    {
        $model = SaCompanyModel::where('subdomain', $subdomain)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Company $company): Company
    {
        $data = [
            'name' => $company->getName(),
            'subdomain' => $company->getSubdomain(),
            'email' => $company->getEmail(),
            'phone' => $company->getPhone(),
            'address' => $company->getAddress(),
            'city' => $company->getCity(),
            'country' => $company->getCountry(),
            'contact_person' => $company->getContactPerson(),
            'currency' => $company->getCurrency(),
            'status' => $company->getStatus(),
        ];

        if ($company->id() === 0) {
            $model = SaCompanyModel::create($data);
        } else {
            $model = SaCompanyModel::find($company->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaCompanyModel $model): Company
    {
        return Company::reconstitute(
            id: CompanyId::fromInt($model->id),
            name: $model->name,
            subdomain: $model->subdomain,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
            city: $model->city,
            country: $model->country,
            contactPerson: $model->contact_person,
            currency: $model->currency,
            status: $model->status ?? 'active',
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
