<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Bin;
use App\Domain\StockFlow\Repositories\BinRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaBinModel;
use DateTimeImmutable;

class EloquentBinRepository implements BinRepositoryInterface
{
    public function findById(BinId $id): ?Bin
    {
        $model = SaBinModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaBinModel::where('sa_company_id', $companyId->toInt())
            ->with('department')
            ->withCount('items')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Bin $bin): Bin
    {
        $data = [
            'sa_company_id' => $bin->getCompanyIdValue(),
            'sa_department_id' => $bin->getDepartmentIdValue(),
            'name' => $bin->getName(),
            'label' => $bin->getLabel(),
            'description' => $bin->getDescription(),
            'sort_order' => $bin->getSortOrder(),
        ];

        if ($bin->id() === 0) {
            $model = SaBinModel::create($data);
        } else {
            $model = SaBinModel::find($bin->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(BinId $id): void
    {
        SaBinModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaBinModel $model): Bin
    {
        return Bin::reconstitute(
            id: BinId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            departmentId: DepartmentId::fromInt($model->sa_department_id),
            name: $model->name,
            label: $model->label,
            description: $model->description,
            sortOrder: $model->sort_order ?? 0,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
