<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Exceptions\WatchlistException;
use App\Domain\GrowStream\Repositories\WatchlistRepositoryInterface;

class WatchlistService
{
    public function __construct(
        private WatchlistRepositoryInterface $watchlistRepo,
    ) {}

    public function addToWatchlist(int $userId, int $videoId, ?string $notes = null): array
    {
        if ($this->watchlistRepo->isInWatchlist($userId, $videoId)) {
            throw WatchlistException::alreadyAdded();
        }

        $watchlist = $this->watchlistRepo->addToWatchlist($userId, $videoId, $notes);

        return $watchlist->toArray();
    }

    public function removeFromWatchlist(int $userId, int $videoId): void
    {
        $this->watchlistRepo->removeFromWatchlist($userId, $videoId);
    }

    public function getUserWatchlist(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->watchlistRepo->getUserWatchlist($userId, $perPage)->toArray();
    }

    public function isInWatchlist(int $userId, int $videoId): bool
    {
        return $this->watchlistRepo->isInWatchlist($userId, $videoId);
    }

    public function getWatchlistCount(int $userId): int
    {
        return $this->watchlistRepo->count($userId);
    }

    public function clearWatchlist(int $userId): void
    {
        $this->watchlistRepo->deleteAll($userId);
    }
}
