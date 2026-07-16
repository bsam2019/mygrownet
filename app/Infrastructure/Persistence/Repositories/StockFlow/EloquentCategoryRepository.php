<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Category;
use App\Domain\StockFlow\Repositories\CategoryRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCategoryModel;
use DateTimeImmutable;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category
    {
        $model = SaCategoryModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaCategoryModel::where('sa_company_id', $companyId->toInt())->orderBy('sort_order')->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findBySlug(CompanyId $companyId, string $slug): ?Category
    {
        $model = SaCategoryModel::where('sa_company_id', $companyId->toInt())->where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findRoots(CompanyId $companyId): array
    {
        return SaCategoryModel::where('sa_company_id', $companyId->toInt())->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findChildren(CategoryId $parentId): array
    {
        return SaCategoryModel::where('parent_id', $parentId->toInt())->orderBy('sort_order')->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(Category $category): Category
    {
        $data = $category->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        $data['parent_id'] = $category->getParentId()?->toInt();
        if ($category->id() === 0) {
            $model = SaCategoryModel::create($data);
        } else {
            $model = SaCategoryModel::find($category->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(CategoryId $id): void { SaCategoryModel::destroy($id->toInt()); }

    private function toDomainEntity(SaCategoryModel $model): Category
    {
        return Category::reconstitute(
            id: CategoryId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name, slug: $model->slug, description: $model->description,
            parentId: $model->parent_id ? CategoryId::fromInt($model->parent_id) : null,
            sortOrder: (int) $model->sort_order,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
