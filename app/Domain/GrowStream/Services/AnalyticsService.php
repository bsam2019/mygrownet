<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;

class AnalyticsService
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private CreatorProfileRepositoryInterface $creatorRepo,
        private VideoViewRepositoryInterface $viewRepo,
        private WatchHistoryRepositoryInterface $watchRepo,
    ) {}

    public function getOverview(int $periodDays = 30): array
    {
        $periodStart = now()->subDays($periodDays);

        $totalVideos = $this->videoRepo->query()->count();
        $publishedVideos = (clone $this->videoRepo->query())->where('is_published', true)->count();
        $newVideos = (clone $this->videoRepo->query())->where('created_at', '>=', $periodStart)->count();

        $viewsQuery = $this->viewRepo->query();
        $periodViews = $viewsQuery->where('viewed_at', '>=', $periodStart)->count();
        $uniqueViewers = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->distinct('user_id')
            ->count('user_id');

        $totalWatchTime = $this->watchRepo->totalWatchTime();
        $completionRate = $this->watchRepo->completionCount();
        $avgWatchDuration = $this->watchRepo->averageWatchDuration();
        $totalWatchHistoryEntries = (clone $this->watchRepo->query())->count();

        $avgCompletionRate = $totalWatchHistoryEntries > 0
            ? round(($completionRate / $totalWatchHistoryEntries) * 100, 2)
            : 0.0;

        $dailyViews = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        $topCategories = (clone $this->videoRepo->query())
            ->join('growstream_video_category_pivot', 'growstream_videos.id', '=', 'growstream_video_category_pivot.video_id')
            ->join('growstream_video_categories', 'growstream_video_category_pivot.category_id', '=', 'growstream_video_categories.id')
            ->selectRaw('growstream_video_categories.name, COUNT(*) as video_count')
            ->groupBy('growstream_video_categories.name')
            ->orderByDesc('video_count')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_videos' => $totalVideos,
            'published_videos' => $publishedVideos,
            'new_videos' => $newVideos,
            'period_views' => $periodViews,
            'total_watch_time_seconds' => $totalWatchTime,
            'unique_viewers' => $uniqueViewers,
            'completion_count' => $completionRate,
            'average_completion_rate' => $avgCompletionRate,
            'average_watch_duration_seconds' => $avgWatchDuration,
            'daily_views' => $dailyViews,
            'top_categories' => $topCategories,
        ];
    }

    public function getVideoAnalytics(int $videoId, int $periodDays = 30): array
    {
        $periodStart = now()->subDays($periodDays);

        $video = $this->videoRepo->findById($videoId);
        if (!$video) {
            return [];
        }

        $totalViews = $this->viewRepo->getTotalViews($videoId);
        $uniqueViewers = $this->viewRepo->getUniqueViewers($videoId);
        $viewsAnalytics = $this->viewRepo->getViewsAnalytics($videoId, 'daily');
        $completionRate = $this->getCompletionRate($videoId);
        $avgWatchTime = $this->getAverageWatchTime($videoId);

        $periodViewsData = $this->viewRepo->getViewsByVideo($videoId, $periodStart, now());

        $deviceBreakdown = (clone $this->viewRepo->query())
            ->where('video_id', $videoId)
            ->where('viewed_at', '>=', $periodStart)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get()
            ->toArray();

        $durationBuckets = (clone $this->viewRepo->query())
            ->where('video_id', $videoId)
            ->where('viewed_at', '>=', $periodStart)
            ->selectRaw("
                CASE
                    WHEN watch_duration < 60 THEN '0-1min'
                    WHEN watch_duration < 300 THEN '1-5min'
                    WHEN watch_duration < 600 THEN '5-10min'
                    ELSE '10min+'
                END as bucket,
                COUNT(*) as count
            ")
            ->groupBy('bucket')
            ->get()
            ->toArray();

        return [
            'video_id' => $videoId,
            'title' => $video->title,
            'total_views' => $totalViews,
            'unique_viewers' => $uniqueViewers,
            'period_views' => $periodViewsData->count(),
            'completion_rate' => $completionRate,
            'average_watch_duration_seconds' => $avgWatchTime,
            'views_analytics' => $viewsAnalytics,
            'daily_views' => $periodViewsData->toArray(),
            'device_breakdown' => $deviceBreakdown,
            'duration_buckets' => $durationBuckets,
        ];
    }

    public function getCreatorAnalytics(int $creatorId, int $periodDays = 30): array
    {
        $periodStart = now()->subDays($periodDays);

        $creator = $this->creatorRepo->findById($creatorId);
        if (!$creator) {
            return [];
        }

        $videosQuery = $this->videoRepo->query()
            ->where('creator_id', $creatorId);

        $totalVideos = (clone $videosQuery)->count();
        $publishedVideos = (clone $videosQuery)->where('is_published', true)->count();
        $newVideos = (clone $videosQuery)->where('created_at', '>=', $periodStart)->count();
        $totalViews = (clone $videosQuery)->sum('view_count');
        $periodViews = (clone $videosQuery)->sum('view_count');

        $videoIds = (clone $videosQuery)->pluck('id')->toArray();

        $periodWatchSeconds = 0;
        $periodCompletions = 0;
        $periodWatchEntries = 0;
        if (!empty($videoIds)) {
            $periodWatchSeconds = (clone $this->watchRepo->query())
                ->whereIn('video_id', $videoIds)
                ->where('last_watched_at', '>=', $periodStart)
                ->sum('current_position');

            $periodCompletions = (clone $this->watchRepo->query())
                ->whereIn('video_id', $videoIds)
                ->where('is_completed', true)
                ->where('completed_at', '>=', $periodStart)
                ->count();

            $periodWatchEntries = (clone $this->watchRepo->query())
                ->whereIn('video_id', $videoIds)
                ->where('last_watched_at', '>=', $periodStart)
                ->count();
        }

        $avgCompletionRate = $periodWatchEntries > 0
            ? round(($periodCompletions / $periodWatchEntries) * 100, 2)
            : 0.0;

        return [
            'creator_id' => $creatorId,
            'channel_name' => $creator->channel_name ?? $creator->display_name,
            'display_name' => $creator->display_name,
            'total_videos' => $totalVideos,
            'published_videos' => $publishedVideos,
            'new_videos' => $newVideos,
            'total_views' => $totalViews,
            'period_views' => $periodViews,
            'period_watch_seconds' => $periodWatchSeconds,
            'period_completions' => $periodCompletions,
            'average_completion_rate' => $avgCompletionRate,
        ];
    }

    public function getEngagement(int $periodDays = 30): array
    {
        $periodStart = now()->subDays($periodDays);

        $activeUsers = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $avgSessionDuration = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->avg('watch_duration');

        $totalCompletions = (clone $this->watchRepo->query())
            ->where('is_completed', true)
            ->where('completed_at', '>=', $periodStart)
            ->count();

        $totalWatchEntries = (clone $this->watchRepo->query())
            ->where('last_watched_at', '>=', $periodStart)
            ->count();

        $avgCompletionRate = $totalWatchEntries > 0
            ? round(($totalCompletions / $totalWatchEntries) * 100, 2)
            : 0.0;

        $returningViewers = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as view_count')
            ->groupBy('user_id')
            ->having('view_count', '>', 1)
            ->get()
            ->count();

        $peakHours = (clone $this->viewRepo->query())
            ->where('viewed_at', '>=', $periodStart)
            ->selectRaw('HOUR(viewed_at) as hour, COUNT(*) as views')
            ->groupBy('hour')
            ->orderByDesc('views')
            ->limit(5)
            ->get()
            ->toArray();

        $contentTypeStats = (clone $this->videoRepo->query())
            ->where('is_published', true)
            ->selectRaw('content_type, COUNT(*) as count, SUM(view_count) as total_views')
            ->groupBy('content_type')
            ->get()
            ->toArray();

        return [
            'active_users' => $activeUsers,
            'average_session_duration_seconds' => round((float) $avgSessionDuration, 2),
            'average_completion_rate' => $avgCompletionRate,
            'total_completions' => $totalCompletions,
            'returning_viewers' => $returningViewers,
            'peak_hours' => $peakHours,
            'content_type_stats' => $contentTypeStats,
        ];
    }

    public function getCompletionRate(int $videoId): float
    {
        $completed = $this->watchRepo->completionCount($videoId);
        $total = (clone $this->watchRepo->query())
            ->where('video_id', $videoId)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        return round(($completed / $total) * 100, 2);
    }

    public function getAverageWatchTime(?int $videoId = null): float
    {
        return round($this->watchRepo->averageWatchDuration($videoId), 2);
    }
}
