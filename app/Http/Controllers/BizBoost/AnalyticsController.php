<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $business = $this->getBusiness($request);

        // Check feature access
        $hasAdvancedAnalytics = $this->subscriptionService->hasFeature($user, 'advanced_analytics', 'bizboost');

        $period = $request->period ?? '30';
        $startDate = now()->subDays((int) $period);

        // Overview stats
        $overview = $this->getOverviewStats($business, $startDate);

        // Daily metrics
        $dailyMetrics = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        // Top posts by engagement
        $topPosts = $business->posts()
            ->where('status', 'published')
            ->where('published_at', '>=', $startDate)
            ->orderByRaw("JSON_EXTRACT(analytics, '$.engagements') DESC")
            ->take(5)
            ->get(['id', 'title', 'caption', 'published_at', 'analytics']);

        // Traffic sources
        $trafficSources = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('recorded_at', '>=', $startDate)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->get();

        return Inertia::render('BizBoost/Analytics/Index', [
            'overview' => $overview,
            'dailyMetrics' => $dailyMetrics,
            'topPosts' => $topPosts,
            'trafficSources' => $trafficSources,
            'hasAdvancedAnalytics' => $hasAdvancedAnalytics,
            'period' => $period,
        ]);
    }

    public function posts(Request $request): Response
    {
        $business = $this->getBusiness($request);

        $posts = $business->posts()
            ->where('status', 'published')
            ->with('media')
            ->orderByDesc('published_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('BizBoost/Analytics/Posts', [
            'posts' => $posts,
        ]);
    }

    public function postDetail(Request $request, int $id): Response
    {
        $business = $this->getBusiness($request);
        $post = $business->posts()->with('media')->findOrFail($id);

        // Get post-specific analytics from events
        $eventCounts = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('post_id', $id)
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        // Calculate analytics summary
        $totalLikes = $eventCounts['like'] ?? 0;
        $totalComments = $eventCounts['comment'] ?? 0;
        $totalShares = $eventCounts['share'] ?? 0;
        $totalReach = $eventCounts['view'] ?? $eventCounts['impression'] ?? 0;

        $engagementRate = $totalReach > 0 
            ? (($totalLikes + $totalComments + $totalShares) / $totalReach) * 100 
            : 0;

        // Get engagement history (last 14 days)
        $engagementHistory = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('post_id', $id)
            ->where('recorded_at', '>=', now()->subDays(14))
            ->select(
                DB::raw('DATE(recorded_at) as date'),
                DB::raw("SUM(CASE WHEN event_type = 'like' THEN 1 ELSE 0 END) as likes"),
                DB::raw("SUM(CASE WHEN event_type = 'comment' THEN 1 ELSE 0 END) as comments"),
                DB::raw("SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) as shares"),
                DB::raw("SUM(CASE WHEN event_type IN ('view', 'impression') THEN 1 ELSE 0 END) as reach")
            )
            ->groupBy(DB::raw('DATE(recorded_at)'))
            ->orderBy('date')
            ->get();

        // Get average metrics for comparison
        $avgMetrics = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->whereNotNull('post_id')
            ->select(
                DB::raw("AVG(CASE WHEN event_type = 'like' THEN 1 ELSE 0 END) * COUNT(DISTINCT post_id) as avg_likes"),
                DB::raw("AVG(CASE WHEN event_type = 'comment' THEN 1 ELSE 0 END) * COUNT(DISTINCT post_id) as avg_comments"),
                DB::raw("AVG(CASE WHEN event_type IN ('view', 'impression') THEN 1 ELSE 0 END) * COUNT(DISTINCT post_id) as avg_reach")
            )
            ->first();

        $avgLikes = $avgMetrics->avg_likes ?? 1;
        $avgComments = $avgMetrics->avg_comments ?? 1;
        $avgReach = $avgMetrics->avg_reach ?? 1;

        $analytics = [
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments,
            'total_shares' => $totalShares,
            'total_reach' => $totalReach,
            'engagement_rate' => round($engagementRate, 2),
            'best_performing_platform' => $post->platforms[0] ?? null,
            'peak_engagement_time' => $this->getPeakEngagementTime($business->id, $id),
        ];

        $comparisons = [
            'likes_vs_avg' => $avgLikes > 0 ? round((($totalLikes - $avgLikes) / $avgLikes) * 100, 1) : 0,
            'comments_vs_avg' => $avgComments > 0 ? round((($totalComments - $avgComments) / $avgComments) * 100, 1) : 0,
            'reach_vs_avg' => $avgReach > 0 ? round((($totalReach - $avgReach) / $avgReach) * 100, 1) : 0,
        ];

        return Inertia::render('BizBoost/Analytics/PostDetail', [
            'post' => $post,
            'analytics' => $analytics,
            'engagementHistory' => $engagementHistory,
            'comparisons' => $comparisons,
        ]);
    }

    private function getPeakEngagementTime(int $businessId, int $postId): ?string
    {
        $peak = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->where('post_id', $postId)
            ->select(DB::raw('HOUR(recorded_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('HOUR(recorded_at)'))
            ->orderByDesc('count')
            ->first();

        if (!$peak) {
            return null;
        }

        $hour = (int) $peak->hour;
        $period = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);

        return "{$displayHour}:00 {$period}";
    }

    private function getOverviewStats(BizBoostBusinessModel $business, $startDate): array
    {
        $previousStart = $startDate->copy()->subDays($startDate->diffInDays(now()));

        // Current period
        $currentPageViews = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->where('date', '>=', $startDate)
            ->sum('page_views');

        $currentEngagements = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->where('date', '>=', $startDate)
            ->sum('post_engagements');

        $currentWhatsappClicks = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->where('date', '>=', $startDate)
            ->sum('whatsapp_clicks');

        $currentQrScans = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->where('date', '>=', $startDate)
            ->sum('qr_scans');

        // Previous period for comparison
        $previousPageViews = DB::table('bizboost_analytics_daily')
            ->where('business_id', $business->id)
            ->whereBetween('date', [$previousStart, $startDate])
            ->sum('page_views');

        return [
            'page_views' => [
                'current' => $currentPageViews,
                'change' => $previousPageViews > 0 
                    ? round((($currentPageViews - $previousPageViews) / $previousPageViews) * 100, 1)
                    : 0,
            ],
            'engagements' => [
                'current' => $currentEngagements,
            ],
            'whatsapp_clicks' => [
                'current' => $currentWhatsappClicks,
            ],
            'qr_scans' => [
                'current' => $currentQrScans,
            ],
            'posts_published' => $business->posts()
                ->where('status', 'published')
                ->where('published_at', '>=', $startDate)
                ->count(),
        ];
    }

    private function getBusiness(Request $request): BizBoostBusinessModel
    {
        return BizBoostBusinessModel::where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
