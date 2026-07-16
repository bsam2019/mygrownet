<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Branch;
use App\Domain\StockFlow\Repositories\BranchRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\BranchId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBranchModel;
use DateTimeImmutable;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function findById(BranchId $id): ?Branch
    {
        $model = SaBranchModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaBranchModel::where('sa_company_id', $companyId->toInt())->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findActive(CompanyId $companyId): array
    {
        return SaBranchModel::where('sa_company_id', $companyId->toInt())->where('is_active', true)->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findHeadOffice(CompanyId $companyId): ?Branch
    {
        $model = SaBranchModel::where('sa_company_id', $companyId->toInt())->where('is_head_office', true)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCode(CompanyId $companyId, string $code): ?Branch
    {
        $model = SaBranchModel::where('sa_company_id', $companyId->toInt())->where('code', $code)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Branch $branch): Branch
    {
        $data = $branch->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($branch->id() === 0) {
            $model = SaBranchModel::create($data);
        } else {
            $model = SaBranchModel::find($branch->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(BranchId $id): void { SaBranchModel::destroy($id->toInt()); }

    private function toDomainEntity(SaBranchModel $model): Branch
    {
        return Branch::reconstitute(
            id: BranchId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name, code: $model->code, phone: $model->phone, email: $model->email,
            address: $model->address, city: $model->city, country: $model->country,
            isHeadOffice: (bool) $model->is_head_office, isActive: (bool) $model->is_active,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
