<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WatchHistoryRepositoryInterface
{
    public function findById(int $id): ?WatchHistory;

    public function findByUserAndVideo(int $userId, int $videoId): ?WatchHistory;

    public function save(array $data): WatchHistory;

    public function updateOrCreate(array $attributes, array $values): WatchHistory;

    public function delete(WatchHistory $history): bool;

    public function forUser(int $userId, array $relations = []): Collection;

    public function paginateForUser(int $userId, int $perPage = 20, array $relations = []): LengthAwarePaginator;

    public function inProgress(int $userId, array $relations = []): Collection;

    public function completed(int $userId, array $relations = []): Collection;

    public function recent(int $userId, int $days = 30, array $relations = []): Collection;

    public function continueWatching(int $userId, int $limit = 10, array $relations = []): Collection;

    public function averageWatchDuration(?int $videoId = null): float;

    public function completionCount(?int $videoId = null): int;

    public function totalWatchTime(?int $videoId = null): int;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
