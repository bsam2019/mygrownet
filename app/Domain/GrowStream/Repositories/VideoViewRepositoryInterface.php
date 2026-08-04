<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use Illuminate\Database\Eloquent\Collection;

interface VideoViewRepositoryInterface
{
    public function findById(int $id): ?VideoView;

    public function recordView(int $videoId, ?int $userId = null, ?string $ip = null, ?string $userAgent = null): VideoView;

    /**
     * @param int $videoId
     * @param \DateTimeInterface|null $from
     * @param \DateTimeInterface|null $to
     */
    public function getViewsByVideo(int $videoId, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): Collection;

    public function getViewsByDate(int $videoId, \DateTimeInterface $date): int;

    public function getTotalViews(int $videoId): int;

    public function getUniqueViewers(int $videoId): int;

    /**
     * @param int $videoId
     * @param string $period daily|weekly|monthly
     * @return array<int, array{date: string, views: int}>
     */
    public function getViewsAnalytics(int $videoId, string $period = 'daily'): array;

    /**
     * Count how many times a user has started playback of videos whose
     * access_level is not 'free' since the given date (viewer allowance).
     */
    public function countPremiumViewsByUser(int $userId, ?\DateTimeInterface $from = null): int;

    public function deleteByVideo(int $videoId): void;

    public function query(): \Illuminate\Database\Eloquent\Builder;
}
