<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface VideoRepositoryInterface
{
    public function findById(int $id): ?Video;

    public function findBySlug(string $slug): ?Video;

    public function findByUuid(string $uuid): ?Video;

    public function findAll(array $relations = []): Collection;

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function save(array $data): Video;

    public function update(Video $video, array $data): Video;

    public function delete(Video $video): bool;

    public function published(array $relations = []): Collection;

    public function paginatePublished(int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function featured(int $limit = 10, array $relations = []): Collection;

    public function trending(int $days = 7, int $limit = 20, array $relations = []): Collection;

    public function search(string $term, array $relations = []): LengthAwarePaginator;

    public function findByContentType(string $type, array $relations = []): Collection;

    public function findByCreator(int $creatorId, array $relations = []): Collection;

    public function findBySeries(int $seriesId, array $relations = []): Collection;

    public function updateInBulk(array $ids, array $data): void;

    public function bulkDelete(array $ids): void;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
