<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Entities\Video as VideoEntity;
use App\Domain\GrowStream\Entities\WatchHistory;
use App\Domain\GrowStream\Exceptions\InsufficientAccessException;
use App\Domain\GrowStream\Exceptions\VideoNotAvailableException;
use App\Domain\GrowStream\Exceptions\VideoNotFoundException;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Domain\GrowStream\ValueObjects\AccessLevel;
use App\Domain\GrowStream\ValueObjects\DeviceType;
use App\Domain\GrowStream\ValueObjects\UploadStatus;
use App\Domain\GrowStream\ValueObjects\VideoId;
use App\Models\User;

class WatchService
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private WatchHistoryRepositoryInterface $watchHistoryRepo,
        private VideoViewRepositoryInterface $viewRepo,
        private ?AccessControlService $accessControl = null,
        private ?RentalService $rentalService = null,
        private ?VideoSeriesRepositoryInterface $seriesRepo = null,
    ) {}

    public function authorizePlayback(int $videoId, int $userId, ?string $ip = null, ?string $userAgent = null): array
    {
        $eloquentVideo = $this->videoRepo->findById($videoId);
        if (! $eloquentVideo) {
            throw VideoNotFoundException::notFound();
        }

        $video = VideoEntity::reconstitute($eloquentVideo->toArray());

        if (! $video->isPublished() || $video->uploadStatus() !== UploadStatus::Ready) {
            throw VideoNotAvailableException::notPublished();
        }

        if (! $this->canAccessVideo($videoId, $userId, $video)) {
            throw InsufficientAccessException::accessDenied();
        }

        $deviceType = $userAgent !== null ? self::detectDeviceType($userAgent) : null;

        $this->viewRepo->recordView($videoId, $userId, $ip, $userAgent);

        $video->incrementViews();
        $this->videoRepo->save($video->toArray());

        $historyData = [
            'user_id' => $userId,
            'video_id' => $videoId,
            'current_position' => 0,
            'duration' => $video->durationSeconds() ?? 0,
            'progress_percentage' => 0.0,
            'is_completed' => false,
            'device_type' => $deviceType?->value,
            'last_watched_at' => now()->toDateTimeString(),
        ];
        $this->watchHistoryRepo->updateOrCreate(
            ['user_id' => $userId, 'video_id' => $videoId],
            $historyData,
        );

        return [
            'access_token' => bin2hex(random_bytes(32)),
            'playback_url' => $video->videoUrl(),
            'video' => $video->toArray(),
            'watch_progress' => [
                'watched_seconds' => 0,
                'progress_percentage' => 0.0,
                'is_completed' => false,
            ],
        ];
    }

    public function updateProgress(int $videoId, int $userId, int $currentPosition, int $duration, ?string $deviceType = null): array
    {
        $eloquent = $this->watchHistoryRepo->findByUserAndVideo($userId, $videoId);

        $history = $eloquent
            ? WatchHistory::reconstitute($eloquent->toArray())
            : WatchHistory::create($userId, VideoId::fromInt($videoId), 0, $deviceType ? DeviceType::fromString($deviceType) : null);

        $additional = max(0, $currentPosition - $history->watchedSeconds());
        $history->resume($additional, $duration);

        $progress = $history->getProgressPercent($duration);
        if ($progress >= 95.0) {
            $history->markCompleted();
        }

        $this->watchHistoryRepo->save($history->toArray());

        return [
            'progress_percentage' => $progress,
            'is_completed' => $history->completed(),
        ];
    }

    public function getHistory(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->watchHistoryRepo->paginateForUser($userId, $perPage)->toArray();
    }

    public function getContinueWatching(int $userId, int $limit = 10): array
    {
        return $this->watchHistoryRepo->continueWatching($userId, $limit)->toArray();
    }

    public function getInProgress(int $userId): array
    {
        return $this->watchHistoryRepo->inProgress($userId)->toArray();
    }

    public function canWatch(int $videoId, ?int $userId): bool
    {
        $eloquent = $this->videoRepo->findById($videoId);
        if (! $eloquent) {
            return false;
        }

        $video = VideoEntity::reconstitute($eloquent->toArray());

        return $this->canAccessVideo($videoId, $userId, $video);
    }

    /**
     * A viewer may watch a video when:
     *  - it is a free episode of its series (episode_number <= free_episode_count),
     *  - their subscription tier covers it, or
     *  - they hold an active pay-per-view rental for the video.
     */
    protected function canAccessVideo(int $videoId, ?int $userId, VideoEntity $video): bool
    {
        if ($this->isFreeEpisode($video)) {
            return true;
        }

        if ($video->canBeAccessedBy($this->resolveUserAccess($userId))) {
            return true;
        }

        if ($userId !== null && $this->rentalService !== null) {
            return $this->rentalService->hasActiveRental($userId, $videoId);
        }

        return false;
    }

    /**
     * Free-first-episode rule (v3): episode 1 (up to the series'
     * free_episode_count) plays without a subscription. Enforced here, at the
     * server-side authorization step, not client-side only.
     */
    protected function isFreeEpisode(VideoEntity $video): bool
    {
        $episodeNumber = $video->episodeNumber();
        if ($episodeNumber === null || $episodeNumber < 1) {
            return false;
        }

        if ($this->seriesRepo === null) {
            return false;
        }

        $series = $this->seriesRepo->findById($video->seriesId());
        if ($series === null) {
            return false;
        }

        return $episodeNumber <= (int) ($series->free_episode_count ?? 1);
    }

    /**
     * Map the viewer to a video access-level rank. Without a paid GrowStream
     * subscription a viewer is treated as 'free'.
     */
    protected function resolveUserAccess(?int $userId): string
    {
        if ($this->accessControl === null || $userId === null) {
            return AccessLevel::Free->value;
        }

        $user = User::find($userId);

        return $user
            && $this->accessControl->hasPaidSubscription($user)
            && $this->accessControl->remainingWatchMinutes($user) !== 0
            ? AccessLevel::Premium->value
            : AccessLevel::Free->value;
    }

    public static function detectDeviceType(?string $userAgent): DeviceType
    {
        if ($userAgent === null) {
            return DeviceType::Desktop;
        }

        $ua = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile|wpdesktop/i', $ua)) {
            return DeviceType::Mobile;
        }

        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return DeviceType::Tablet;
        }

        return DeviceType::Desktop;
    }
}
