<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VideoCategoryRepositoryInterface
{
    public function findById(int $id): ?VideoCategory;

    public function findBySlug(string $slug): ?VideoCategory;

    public function findAll(array $relations = []): Collection;

    public function save(array $data): VideoCategory;

    public function update(VideoCategory $category, array $data): VideoCategory;

    public function delete(VideoCategory $category): bool;

    public function active(array $relations = []): Collection;

    public function rootCategories(array $relations = []): Collection;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
