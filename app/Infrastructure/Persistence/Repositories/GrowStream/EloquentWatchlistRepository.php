<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Repositories\WatchlistRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentWatchlistRepository implements WatchlistRepositoryInterface
{
    public function findById(int $id): ?Watchlist
    {
        return Watchlist::find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Watchlist::where('user_id', $userId)->get();
    }

    public function findByVideo(int $videoId): Collection
    {
        return Watchlist::where('watchlistable_type', Video::class)
            ->where('watchlistable_id', $videoId)
            ->get();
    }

    public function addToWatchlist(int $userId, int $videoId, ?string $notes = null): Watchlist
    {
        return Watchlist::firstOrCreate(
            [
                'user_id' => $userId,
                'watchlistable_type' => Video::class,
                'watchlistable_id' => $videoId,
            ],
            [
                'added_at' => now(),
            ]
        );
    }

    public function removeFromWatchlist(int $userId, int $videoId): bool
    {
        return Watchlist::where('user_id', $userId)
            ->where('watchlistable_type', Video::class)
            ->where('watchlistable_id', $videoId)
            ->delete() > 0;
    }

    public function isInWatchlist(int $userId, int $videoId): bool
    {
        return Watchlist::where('user_id', $userId)
            ->where('watchlistable_type', Video::class)
            ->where('watchlistable_id', $videoId)
            ->exists();
    }

    public function getUserWatchlist(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Watchlist::with('watchlistable')
            ->where('user_id', $userId)
            ->orderBy('added_at', 'desc')
            ->paginate($perPage);
    }

    public function count(?int $userId = null): int
    {
        $query = Watchlist::query();
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->count();
    }

    public function deleteAll(int $userId): void
    {
        Watchlist::where('user_id', $userId)->delete();
    }

    public function query(): Builder
    {
        return Watchlist::query();
    }
}
