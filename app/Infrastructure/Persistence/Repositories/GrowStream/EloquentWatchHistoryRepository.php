<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentWatchHistoryRepository implements WatchHistoryRepositoryInterface
{
    public function findById(int $id): ?WatchHistory
    {
        return WatchHistory::find($id);
    }

    public function findByUserAndVideo(int $userId, int $videoId): ?WatchHistory
    {
        return WatchHistory::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();
    }

    public function save(array $data): WatchHistory
    {
        return WatchHistory::create($data);
    }

    public function updateOrCreate(array $attributes, array $values): WatchHistory
    {
        return WatchHistory::updateOrCreate($attributes, $values);
    }

    public function delete(WatchHistory $history): bool
    {
        return $history->delete();
    }

    public function forUser(int $userId, array $relations = []): Collection
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->get();
    }

    public function paginateForUser(int $userId, int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->orderBy('last_watched_at', 'desc')
            ->paginate($perPage);
    }

    public function inProgress(int $userId, array $relations = []): Collection
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->inProgress()
            ->get();
    }

    public function completed(int $userId, array $relations = []): Collection
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->completed()
            ->get();
    }

    public function recent(int $userId, int $days = 30, array $relations = []): Collection
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->recent($days)
            ->get();
    }

    public function continueWatching(int $userId, int $limit = 10, array $relations = []): Collection
    {
        return WatchHistory::with($relations)
            ->where('user_id', $userId)
            ->inProgress()
            ->latest('last_watched_at')
            ->limit($limit)
            ->get();
    }

    public function averageWatchDuration(?int $videoId = null): float
    {
        $query = WatchHistory::query();
        if ($videoId) {
            $query->where('video_id', $videoId);
        }
        return (float) $query->avg('watch_duration');
    }

    public function completionCount(?int $videoId = null): int
    {
        $query = WatchHistory::where('is_completed', true);
        if ($videoId) {
            $query->where('video_id', $videoId);
        }
        return $query->count();
    }

    public function totalWatchTime(?int $videoId = null): int
    {
        $query = WatchHistory::query();
        if ($videoId) {
            $query->where('video_id', $videoId);
        }
        return (int) $query->sum('watch_duration');
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return WatchHistory::query();
    }
}
