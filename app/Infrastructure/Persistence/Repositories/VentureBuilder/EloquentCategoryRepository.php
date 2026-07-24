<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = VentureCategoryModel::find($id);
        return $model ? $model->toArray() : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $model = VentureCategoryModel::where('slug', $slug)->first();
        return $model ? $model->toArray() : null;
    }

    public function getActive(): array
    {
        return VentureCategoryModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }
}
