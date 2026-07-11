<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Supplier;
use App\Domain\StockFlow\Repositories\SupplierRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSupplierModel;
use DateTimeImmutable;

class EloquentSupplierRepository implements SupplierRepositoryInterface
{
    public function findById(SupplierId $id): ?Supplier
    {
        $model = SaSupplierModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaSupplierModel::where('sa_company_id', $companyId->toInt())
            ->withCount('purchaseOrders')
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Supplier $supplier): Supplier
    {
        $data = [
            'sa_company_id' => $supplier->getCompanyId()->toInt(),
            'name' => $supplier->getName(),
            'contact_person' => $supplier->getContactPerson(),
            'phone' => $supplier->getPhone(),
            'email' => $supplier->getEmail(),
            'address' => $supplier->getAddress(),
            'payment_terms' => $supplier->getPaymentTerms(),
        ];

        if ($supplier->id() === 0) {
            $model = SaSupplierModel::create($data);
        } else {
            $model = SaSupplierModel::find($supplier->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(SupplierId $id): void
    {
        SaSupplierModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaSupplierModel $model): Supplier
    {
        return Supplier::reconstitute(
            id: SupplierId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            contactPerson: $model->contact_person,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            paymentTerms: $model->payment_terms,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
