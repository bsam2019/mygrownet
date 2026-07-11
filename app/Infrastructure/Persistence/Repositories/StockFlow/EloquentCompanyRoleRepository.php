<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\CompanyRole;
use App\Domain\StockFlow\Repositories\CompanyRoleRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyRoleModel;

class EloquentCompanyRoleRepository implements CompanyRoleRepositoryInterface
{
    public function findById(CompanyRoleId $id): ?CompanyRole
    {
        $model = SaCompanyRoleModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaCompanyRoleModel::where('sa_company_id', $companyId->toInt())
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndSlug(CompanyId $companyId, string $slug): ?CompanyRole
    {
        $model = SaCompanyRoleModel::where('sa_company_id', $companyId->toInt())
            ->where('slug', $slug)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(CompanyRole $role): CompanyRole
    {
        $data = [
            'sa_company_id' => $role->getCompanyId()->toInt(),
            'name' => $role->getName(),
            'slug' => $role->getSlug(),
            'description' => $role->getDescription(),
            'permissions' => $role->getPermissions(),
            'is_system' => $role->isSystem(),
        ];

        if ($role->id() === 0) {
            $model = SaCompanyRoleModel::create($data);
        } else {
            $model = SaCompanyRoleModel::find($role->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(CompanyRoleId $id): void
    {
        $model = SaCompanyRoleModel::find($id->toInt());
        if ($model && !$model->is_system) {
            $model->delete();
        }
    }

    private function toDomainEntity(SaCompanyRoleModel $model): CompanyRole
    {
        return CompanyRole::reconstitute(
            id: CompanyRoleId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            permissions: $model->permissions ?? [],
            isSystem: $model->is_system,
            createdAt: new \DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new \DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}