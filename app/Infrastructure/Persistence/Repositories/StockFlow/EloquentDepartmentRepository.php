<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Department;
use App\Domain\StockFlow\Repositories\DepartmentRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaDepartmentModel;
use DateTimeImmutable;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function findById(DepartmentId $id): ?Department
    {
        $model = SaDepartmentModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaDepartmentModel::where('sa_company_id', $companyId->toInt())
            ->withCount('bins')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(Department $department): Department
    {
        $data = [
            'sa_company_id' => $department->getCompanyIdValue(),
            'name' => $department->getName(),
            'slug' => str($department->getName())->slug(),
            'description' => $department->getDescription(),
            'sort_order' => $department->getSortOrder(),
        ];

        if ($department->id() === 0) {
            $model = SaDepartmentModel::create($data);
        } else {
            $model = SaDepartmentModel::find($department->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(DepartmentId $id): void
    {
        SaDepartmentModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaDepartmentModel $model): Department
    {
        return Department::reconstitute(
            id: DepartmentId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            sortOrder: $model->sort_order ?? 0,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
