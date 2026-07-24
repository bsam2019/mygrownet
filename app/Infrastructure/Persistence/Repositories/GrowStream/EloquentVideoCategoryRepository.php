<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Repositories\VideoCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentVideoCategoryRepository implements VideoCategoryRepositoryInterface
{
    public function findById(int $id): ?VideoCategory
    {
        return VideoCategory::find($id);
    }

    public function findBySlug(string $slug): ?VideoCategory
    {
        return VideoCategory::where('slug', $slug)->first();
    }

    public function findAll(array $relations = []): Collection
    {
        return VideoCategory::with($relations)->get();
    }

    public function save(array $data): VideoCategory
    {
        return VideoCategory::create($data);
    }

    public function update(VideoCategory $category, array $data): VideoCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(VideoCategory $category): bool
    {
        return $category->delete();
    }

    public function active(array $relations = []): Collection
    {
        return VideoCategory::active()->with($relations)->get();
    }

    public function rootCategories(array $relations = []): Collection
    {
        return VideoCategory::rootCategories()->with($relations)->get();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return VideoCategory::query();
    }
}
