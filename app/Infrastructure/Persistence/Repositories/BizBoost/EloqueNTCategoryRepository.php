<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Category;
use App\Domain\BizBoost\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostCategoryModel;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?Category
    {
        $model = BizBoostCategoryModel::find($id);
        return $model ? Category::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostCategoryModel::where('business_id', $businessId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($m) => Category::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByName(int $businessId, string $name): ?Category
    {
        $model = BizBoostCategoryModel::where('business_id', $businessId)
            ->where('name', $name)
            ->first();
        return $model ? Category::reconstitute($model->toArray()) : null;
    }

    public function save(Category $entity): Category
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostCategoryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostCategoryModel::create($data);
        return Category::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostCategoryModel::destroy($id);
    }

    public function getNextSortOrder(int $businessId): int
    {
        return (BizBoostCategoryModel::where('business_id', $businessId)->max('sort_order') ?? 0) + 1;
    }
}