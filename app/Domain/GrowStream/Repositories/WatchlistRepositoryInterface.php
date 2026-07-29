<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WatchlistRepositoryInterface
{
    public function findById(int $id): ?Watchlist;

    public function findByUser(int $userId): Collection;

    public function findByVideo(int $videoId): Collection;

    public function addToWatchlist(int $userId, int $videoId, ?string $notes = null): Watchlist;

    public function removeFromWatchlist(int $userId, int $videoId): bool;

    public function isInWatchlist(int $userId, int $videoId): bool;

    public function getUserWatchlist(int $userId, int $perPage = 20): LengthAwarePaginator;

    public function count(?int $userId = null): int;

    public function deleteAll(int $userId): void;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
