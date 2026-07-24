<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VideoSeriesRepositoryInterface
{
    public function findById(int $id): ?VideoSeries;

    public function findBySlug(string $slug): ?VideoSeries;

    public function findAll(array $relations = []): Collection;

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function save(array $data): VideoSeries;

    public function update(VideoSeries $series, array $data): VideoSeries;

    public function delete(VideoSeries $series): bool;

    public function published(array $relations = []): Collection;

    public function paginatePublished(int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function search(string $term, array $relations = []): LengthAwarePaginator;

    public function findByType(string $type, array $relations = []): Collection;

    public function findByCreator(int $creatorId, array $relations = []): Collection;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
