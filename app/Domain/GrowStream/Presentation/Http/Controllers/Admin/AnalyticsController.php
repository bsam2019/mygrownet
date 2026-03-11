<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get platform overview analytics
     */
    public function overview(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days

        $startDate = now()->subDays($period);

        // Total videos
        $totalVideos = Video::count();
        $publishedVideos = Video::published()->count();
        $newVideosThisPeriod = Video::where('created_at', '>=', $startDate)->count();

        // Total views
        $totalViews = VideoView::count();
        $viewsThisPeriod = VideoView::where('created_at', '>=', $startDate)->count();

        // Watch time (in hours)
        $totalWatchTime = WatchHistory::sum('watch_duration') / 3600;
        $watchTimeThisPeriod = WatchHistory::where('created_at', '>=', $startDate)
            ->sum('watch_duration') / 3600;

        // Unique viewers
        $uniqueViewers = VideoView::distinct('user_id')->count('user_id');
        $uniqueViewersThisPeriod = VideoView::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');

        // Completion rate
        $completedViews = WatchHistory::where('is_completed', true)->count();
        $totalWatchSessions = WatchHistory::count();
        $completionRate = $totalWatchSessions > 0 
            ? round(($completedViews / $totalWatchSessions) * 100, 2) 
            : 0;

        // Average watch duration
        $avgWatchDuration = WatchHistory::avg('watch_duration');

        // Top categories
        $topCategories = DB::table('growstream_video_categories as c')
            ->join('growstream_video_category as vc', 'c.id', '=', 'vc.category_id')
            ->join('growstream_videos as v', 'vc.video_id', '=', 'v.id')
            ->join('growstream_video_views as vv', 'v.id', '=', 'vv.video_id')
            ->select('c.name', DB::raw('COUNT(vv.id) as view_count'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        // Daily views trend
        $dailyViews = VideoView::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT user_id) as unique_viewers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_videos' => $totalVideos,
                    'published_videos' => $publishedVideos,
                    'new_videos_this_period' => $newVideosThisPeriod,
                    'total_views' => $totalViews,
                    'views_this_period' => $viewsThisPeriod,
                    'total_watch_time_hours' => round($totalWatchTime, 2),
                    'watch_time_this_period_hours' => round($watchTimeThisPeriod, 2),
                    'unique_viewers' => $uniqueViewers,
                    'unique_viewers_this_period' => $uniqueViewersThisPeriod,
                    'completion_rate' => $completionRate,
                    'avg_watch_duration_seconds' => round($avgWatchDuration, 2),
                ],
                'top_categories' => $topCategories,
                'daily_views' => $dailyViews,
            ],
        ]);
    }

    /**
     * Get video analytics
     */
    public function videoAnalytics(Request $request): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $query = Video::with(['creator', 'categories'])
            ->withCount([
                'views',
                'views as recent_views_count' => function ($q) use ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                },
            ])
            ->withSum('watchHistory as total_watch_time', 'watch_duration');

        // Sort by views
        $sortBy = $request->get('sort_by', 'views_count');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $videos = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $videos->items(),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
                'last_page' => $videos->lastPage(),
            ],
        ]);
    }

    /**
     * Get detailed analytics for a specific video
     */
    public function videoDetails(Request $request, Video $video): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Basic stats
        $totalViews = $video->views()->count();
        $uniqueViewers = $video->views()->distinct('user_id')->count('user_id');
        $totalWatchTime = $video->watchHistory()->sum('watch_duration');
        $avgWatchTime = $video->watchHistory()->avg('watch_duration');
        $completionRate = $this->calculateCompletionRate($video);

        // Views over time
        $dailyViews = $video->views()
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT user_id) as unique_viewers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Watch duration distribution
        $durationBuckets = $video->watchHistory()
            ->select(
                DB::raw('CASE 
                    WHEN watch_duration < 60 THEN "0-1min"
                    WHEN watch_duration < 300 THEN "1-5min"
                    WHEN watch_duration < 900 THEN "5-15min"
                    WHEN watch_duration < 1800 THEN "15-30min"
                    ELSE "30min+"
                END as bucket'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('bucket')
            ->get();

        // Device breakdown
        $deviceBreakdown = $video->views()
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get();

        // Geographic data
        $topCountries = $video->views()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'video' => $video->load(['creator', 'categories', 'series']),
                'stats' => [
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
                    'avg_watch_time_seconds' => round($avgWatchTime, 2),
                    'completion_rate' => $completionRate,
                ],
                'daily_views' => $dailyViews,
                'duration_distribution' => $durationBuckets,
                'device_breakdown' => $deviceBreakdown,
                'top_countries' => $topCountries,
            ],
        ]);
    }

    /**
     * Get creator analytics
     */
    public function creatorAnalytics(Request $request): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $creators = CreatorProfile::with('user')
            ->withCount([
                'videos',
                'videos as published_videos_count' => function ($q) {
                    $q->where('is_published', true);
                },
            ])
            ->get()
            ->map(function ($creator) use ($startDate) {
                $totalViews = VideoView::whereHas('video', function ($q) use ($creator) {
                    $q->where('creator_id', $creator->user_id);
                })->count();

                $recentViews = VideoView::whereHas('video', function ($q) use ($creator) {
                    $q->where('creator_id', $creator->user_id);
                })->where('created_at', '>=', $startDate)->count();

                $totalWatchTime = WatchHistory::whereHas('video', function ($q) use ($creator) {
                    $q->where('creator_id', $creator->user_id);
                })->sum('watch_duration');

                return [
                    'creator' => $creator,
                    'stats' => [
                        'total_videos' => $creator->videos_count,
                        'published_videos' => $creator->published_videos_count,
                        'total_views' => $totalViews,
                        'recent_views' => $recentViews,
                        'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
                        'total_revenue' => $creator->total_revenue,
                    ],
                ];
            })
            ->sortByDesc('stats.total_views')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $creators,
        ]);
    }

    /**
     * Get engagement analytics
     */
    public function engagement(Request $request): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Active users (users who watched at least one video)
        $activeUsers = WatchHistory::where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count('user_id');

        // Average session duration
        $avgSessionDuration = WatchHistory::where('created_at', '>=', $startDate)
            ->avg('watch_duration');

        // Completion rate
        $completedViews = WatchHistory::where('created_at', '>=', $startDate)
            ->where('is_completed', true)
            ->count();
        $totalViews = WatchHistory::where('created_at', '>=', $startDate)->count();
        $completionRate = $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0;

        // Returning viewers
        $returningViewers = DB::table('growstream_watch_history')
            ->select('user_id', DB::raw('COUNT(DISTINCT DATE(created_at)) as days_active'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('user_id')
            ->having('days_active', '>', 1)
            ->count();

        // Peak viewing hours
        $peakHours = VideoView::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as views')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Most watched content types
        $contentTypeStats = Video::select('content_type', DB::raw('COUNT(*) as video_count'))
            ->withCount('views')
            ->groupBy('content_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'active_users' => $activeUsers,
                'avg_session_duration_seconds' => round($avgSessionDuration, 2),
                'completion_rate' => $completionRate,
                'returning_viewers' => $returningViewers,
                'peak_hours' => $peakHours,
                'content_type_stats' => $contentTypeStats,
            ],
        ]);
    }

    /**
     * Get revenue analytics (placeholder for future implementation)
     */
    public function revenue(Request $request): JsonResponse
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Placeholder data - will be implemented when payment system is integrated
        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => 0,
                'revenue_this_period' => 0,
                'creator_payouts' => 0,
                'platform_revenue' => 0,
                'revenue_by_access_level' => [],
                'daily_revenue' => [],
            ],
            'message' => 'Revenue tracking will be available after payment integration',
        ]);
    }

    /**
     * Calculate completion rate for a video
     */
    protected function calculateCompletionRate(Video $video): float
    {
        $totalWatches = $video->watchHistory()->count();
        if ($totalWatches === 0) {
            return 0;
        }

        $completedWatches = $video->watchHistory()->where('is_completed', true)->count();
        
        return round(($completedWatches / $totalWatches) * 100, 2);
    }
}
