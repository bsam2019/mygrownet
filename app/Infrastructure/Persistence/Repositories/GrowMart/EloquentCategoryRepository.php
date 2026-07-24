<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Models\GrowMart\GrowMartCategory;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowMartCategory::with('parent')->find($id);
        return $model?->toArray();
    }

    public function findAll(array $filters = []): array
    {
        $query = GrowMartCategory::with('parent')
            ->withCount('products');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->toArray();
    }

    public function findActive(): array
    {
        return GrowMartCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function findParentCategories(): array
    {
        return GrowMartCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function findWithChildren(int $id): ?array
    {
        $model = GrowMartCategory::with('parent')->find($id);
        return $model?->toArray();
    }

    public function countAll(): int
    {
        return GrowMartCategory::count();
    }

    public function save(array $data): array
    {
        $model = GrowMartCategory::create($data);
        return $model->fresh()->toArray();
    }

    public function update(int $id, array $data): array
    {
        $model = GrowMartCategory::findOrFail($id);
        $model->update($data);
        return $model->fresh()->toArray();
    }

    public function delete(int $id): bool
    {
        return GrowMartCategory::destroy($id) > 0;
    }

    public function productCount(int $categoryId): int
    {
        return GrowMartCategory::find($categoryId)?->products()->count() ?? 0;
    }
}
