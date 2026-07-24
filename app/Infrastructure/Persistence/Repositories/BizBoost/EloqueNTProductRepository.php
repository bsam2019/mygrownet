<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Product;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostProductModel;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        $model = BizBoostProductModel::find($id);
        return $model ? Product::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostProductModel::where('business_id', $businessId);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('sort_order')->orderByDesc('created_at')->get()
            ->map(fn($m) => Product::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostProductModel::where('business_id', $businessId)
            ->where('is_active', true);

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('sort_order')->get()
            ->map(fn($m) => Product::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Product $entity): Product
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostProductModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostProductModel::create($data);
        return Product::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostProductModel::destroy($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return BizBoostProductModel::where('business_id', $businessId)->count();
    }

    public function getCategories(int $businessId): array
    {
        return BizBoostProductModel::where('business_id', $businessId)
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }
        return $query;
    }
}