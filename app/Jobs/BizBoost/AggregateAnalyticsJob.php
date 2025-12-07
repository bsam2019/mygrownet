<?php

namespace App\Jobs\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 300;

    public function __construct(
        private ?int $businessId = null,
        private ?string $date = null
    ) {}

    public function handle(): void
    {
        $date = $this->date ? \Carbon\Carbon::parse($this->date) : now()->subDay();
        $dateString = $date->toDateString();

        if ($this->businessId) {
            $this->aggregateForBusiness($this->businessId, $dateString);
        } else {
            $this->aggregateAllBusinesses($dateString);
        }
    }

    private function aggregateAllBusinesses(string $date): void
    {
        $businesses = BizBoostBusinessModel::where('is_active', true)->pluck('id');

        foreach ($businesses as $businessId) {
            $this->aggregateForBusiness($businessId, $date);
        }

        Log::info("Aggregated analytics for {$businesses->count()} businesses on {$date}");
    }

    private function aggregateForBusiness(int $businessId, string $date): void
    {
        $startOfDay = "{$date} 00:00:00";
        $endOfDay = "{$date} 23:59:59";

        // Get event counts
        $events = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->whereBetween('recorded_at', [$startOfDay, $endOfDay])
            ->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        // Get unique visitors
        $uniqueVisitors = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->whereBetween('recorded_at', [$startOfDay, $endOfDay])
            ->where('event_type', 'page_view')
            ->distinct('ip_address')
            ->count('ip_address');

        // Get top posts by engagement
        $topPosts = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->whereBetween('recorded_at', [$startOfDay, $endOfDay])
            ->whereNotNull('post_id')
            ->select('post_id', DB::raw('COUNT(*) as engagement'))
            ->groupBy('post_id')
            ->orderByDesc('engagement')
            ->limit(5)
            ->pluck('engagement', 'post_id')
            ->toArray();

        // Get traffic sources
        $trafficSources = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->whereBetween('recorded_at', [$startOfDay, $endOfDay])
            ->where('event_type', 'page_view')
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        // Upsert daily aggregate
        DB::table('bizboost_analytics_daily')->updateOrInsert(
            [
                'business_id' => $businessId,
                'date' => $date,
            ],
            [
                'page_views' => $events['page_view'] ?? 0,
                'unique_visitors' => $uniqueVisitors,
                'post_impressions' => $events['post_impression'] ?? 0,
                'post_engagements' => $events['post_engagement'] ?? 0,
                'link_clicks' => $events['link_click'] ?? 0,
                'whatsapp_clicks' => $events['whatsapp_click'] ?? 0,
                'qr_scans' => $events['qr_scan'] ?? 0,
                'top_posts' => json_encode($topPosts),
                'traffic_sources' => json_encode($trafficSources),
                'updated_at' => now(),
            ]
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AggregateAnalyticsJob failed: {$exception->getMessage()}");
    }
}
yticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public ?int $businessId = null,
        public ?string $date = null
    ) {}

    public function handle(): void
    {
        $date = $this->date ? \Carbon\Carbon::parse($this->date) : now()->subDay();
        $dateString = $date->format('Y-m-d');

        if ($this->businessId) {
            $this->aggregateForBusiness($this->businessId, $dateString);
        } else {
            $this->aggregateForAllBusinesses($dateString);
        }
    }

    private function aggregateForAllBusinesses(string $date): void
    {
        $businesses = BizBoostBusinessModel::where('is_active', true)->pluck('id');

        foreach ($businesses as $businessId) {
            $this->aggregateForBusiness($businessId, $date);
        }

        Log::info("AggregateAnalyticsJob: Aggregated analytics for {$businesses->count()} businesses on {$date}");
    }

    private function aggregateForBusiness(int $businessId, string $date): void
    {
        $startOfDay = \Carbon\Carbon::parse($date)->startOfDay();
        $endOfDay = \Carbon\Carbon::parse($date)->endOfDay();

        // Get raw events for the day
        $events = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->whereBetween('recorded_at', [$startOfDay, $endOfDay])
            ->get();

        // Calculate metrics
        $pageViews = $events->where('event_type', 'page_view')->count();
        $uniqueVisitors = $events->where('event_type', 'page_view')->unique('ip_address')->count();
        $postImpressions = $events->where('event_type', 'post_impression')->count();
        $postEngagements = $events->whereIn('event_type', ['post_like', 'post_comment', 'post_share'])->count();
        $linkClicks = $events->where('event_type', 'link_click')->count();
        $whatsappClicks = $events->where('event_type', 'whatsapp_click')->count();
        $qrScans = $events->where('event_type', 'qr_scan')->count();

        // Get top posts by engagement
        $topPosts = $events->whereIn('event_type', ['post_like', 'post_comment', 'post_share', 'post_impression'])
            ->whereNotNull('post_id')
            ->groupBy('post_id')
            ->map(fn($group) => [
                'post_id' => $group->first()->post_id,
                'engagements' => $group->count(),
            ])
            ->sortByDesc('engagements')
            ->take(5)
            ->values()
            ->toArray();

        // Get traffic sources
        $trafficSources = $events->where('event_type', 'page_view')
            ->groupBy('source')
            ->map(fn($group, $source) => [
                'source' => $source ?: 'direct',
                'count' => $group->count(),
            ])
            ->values()
            ->toArray();

        // Upsert daily aggregate
        DB::table('bizboost_analytics_daily')->updateOrInsert(
            [
                'business_id' => $businessId,
                'date' => $date,
            ],
            [
                'page_views' => $pageViews,
                'unique_visitors' => $uniqueVisitors,
                'post_impressions' => $postImpressions,
                'post_engagements' => $postEngagements,
                'link_clicks' => $linkClicks,
                'whatsapp_clicks' => $whatsappClicks,
                'qr_scans' => $qrScans,
                'top_posts' => json_encode($topPosts),
                'traffic_sources' => json_encode($trafficSources),
                'updated_at' => now(),
            ]
        );

        Log::info("AggregateAnalyticsJob: Aggregated analytics for business {$businessId} on {$date}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AggregateAnalyticsJob failed: " . $exception->getMessage());
    }
}
