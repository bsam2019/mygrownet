<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceCategory;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function getActiveCategories(): array
    {
        return MarketplaceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    public function findById(int $id): ?array
    {
        $model = MarketplaceCategory::find($id);
        return $model ? $model->toArray() : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $model = MarketplaceCategory::where('slug', $slug)->first();
        return $model ? $model->toArray() : null;
    }

    public function getParentCategories(): array
    {
        return MarketplaceCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }
}
