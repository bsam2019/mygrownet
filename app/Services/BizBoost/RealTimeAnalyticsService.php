<?php

namespace App\Services\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RealTimeAnalyticsService
{
    public function getRealTimeMetrics(BizBoostBusinessModel $business): array
    {
        $cacheKey = "bizboost_realtime_{$business->id}";
        
        return Cache::remember($cacheKey, 60, function () use ($business) {
            $now = now();
            $last24Hours = $now->copy()->subHours(24);
            $last7Days = $now->copy()->subDays(7);
            
            return [
                'live' => $this->getLiveMetrics($business, $last24Hours),
                'trending' => $this->getTrendingContent($business, $last7Days),
                'channels' => $this->getChannelPerformance($business, $last7Days),
                'engagement' => $this->getEngagementMetrics($business, $last24Hours),
                'revenue' => $this->getRevenueAnalytics($business, $last7Days),
            ];
        });
    }

    private function getLiveMetrics(BizBoostBusinessModel $business, Carbon $since): array
    {
        $events = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('recorded_at', '>=', $since)
            ->get();

        $impressions = $events->whereIn('event_type', ['page_view', 'post_impression'])->count();
        $clicks = $events->where('event_type', 'link_click')->count();
        $conversions = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => $conversions,
            'conversion_rate' => $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0,
            'active_now' => $this->getActiveVisitors($business),
        ];
    }

    private function getActiveVisitors(BizBoostBusinessModel $business): int
    {
        return DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('recorded_at', '>=', now()->subMinutes(5))
            ->distinct('ip_address')
            ->count('ip_address');
    }

    private function getTrendingContent(BizBoostBusinessModel $business, Carbon $since): array
    {
        $posts = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('recorded_at', '>=', $since)
            ->whereNotNull('post_id')
            ->select('post_id', DB::raw('COUNT(*) as engagement_count'))
            ->groupBy('post_id')
            ->orderByDesc('engagement_count')
            ->limit(5)
            ->get();

        return $posts->map(function ($post) use ($business) {
            $postData = $business->posts()->find($post->post_id);
            return [
                'id' => $post->post_id,
                'title' => $postData?->caption ?? 'Unknown',
                'engagement' => $post->engagement_count,
                'trend' => $this->calculateTrend($business->id, $post->post_id),
            ];
        })->toArray();
    }

    private function calculateTrend(int $businessId, int $postId): string
    {
        $last24h = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->where('post_id', $postId)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->count();

        $previous24h = DB::table('bizboost_analytics_events')
            ->where('business_id', $businessId)
            ->where('post_id', $postId)
            ->whereBetween('recorded_at', [now()->subHours(48), now()->subHours(24)])
            ->count();

        if ($previous24h == 0) return 'new';
        
        $change = (($last24h - $previous24h) / $previous24h) * 100;
        
        if ($change > 20) return 'rising';
        if ($change < -20) return 'falling';
        return 'stable';
    }

    private function getChannelPerformance(BizBoostBusinessModel $business, Carbon $since): array
    {
        $channels = ['whatsapp', 'facebook', 'instagram', 'twitter', 'linkedin'];
        $performance = [];

        foreach ($channels as $channel) {
            $metrics = DB::table('bizboost_analytics_events')
                ->where('business_id', $business->id)
                ->where('recorded_at', '>=', $since)
                ->where('source', $channel)
                ->selectRaw('
                    COUNT(*) as total_events,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    SUM(CASE WHEN event_type = "link_click" THEN 1 ELSE 0 END) as clicks
                ')
                ->first();

            if ($metrics && $metrics->total_events > 0) {
                $performance[$channel] = [
                    'impressions' => $metrics->total_events,
                    'unique_visitors' => $metrics->unique_visitors,
                    'clicks' => $metrics->clicks,
                    'ctr' => $metrics->total_events > 0 
                        ? round(($metrics->clicks / $metrics->total_events) * 100, 2) 
                        : 0,
                ];
            }
        }

        return $performance;
    }

    private function getEngagementMetrics(BizBoostBusinessModel $business, Carbon $since): array
    {
        $hourlyData = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('recorded_at', '>=', $since)
            ->selectRaw('HOUR(recorded_at) as hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(recorded_at)'))
            ->orderBy('hour')
            ->get();

        $peakHour = $hourlyData->sortByDesc('count')->first();

        return [
            'hourly_breakdown' => $hourlyData->toArray(),
            'peak_hour' => $peakHour ? $this->formatHour($peakHour->hour) : null,
            'peak_engagement' => $peakHour?->count ?? 0,
        ];
    }

    private function formatHour(int $hour): string
    {
        $period = $hour >= 12 ? 'PM' : 'AM';
        $displayHour = $hour > 12 ? $hour - 12 : ($hour === 0 ? 12 : $hour);
        return "{$displayHour}:00 {$period}";
    }

    private function getRevenueAnalytics(BizBoostBusinessModel $business, Carbon $since): array
    {
        $sales = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(amount) as total_revenue,
                AVG(amount) as avg_order_value,
                DATE(created_at) as date
            ')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $totalRevenue = $sales->sum('total_revenue');
        $previousPeriod = DB::table('bizboost_sales')
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$since->copy()->subDays(7), $since])
            ->sum('amount');

        $trend = $previousPeriod > 0 
            ? round((($totalRevenue - $previousPeriod) / $previousPeriod) * 100, 1)
            : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_sales' => $sales->sum('total_sales'),
            'avg_order_value' => $sales->avg('avg_order_value') ?? 0,
            'trend' => $trend,
            'daily_breakdown' => $sales->toArray(),
        ];
    }

    public function getCustomerJourney(BizBoostBusinessModel $business, int $customerId): array
    {
        $events = DB::table('bizboost_analytics_events')
            ->where('business_id', $business->id)
            ->where('customer_id', $customerId)
            ->orderBy('recorded_at')
            ->get();

        $stages = [
            'discovery' => $events->where('event_type', 'page_view')->count(),
            'engagement' => $events->whereIn('event_type', ['post_like', 'post_comment'])->count(),
            'consideration' => $events->where('event_type', 'product_view')->count(),
            'conversion' => DB::table('bizboost_sales')
                ->where('business_id', $business->id)
                ->where('customer_id', $customerId)
                ->count(),
        ];

        return [
            'stages' => $stages,
            'timeline' => $events->map(fn($e) => [
                'type' => $e->event_type,
                'timestamp' => $e->recorded_at,
            ])->toArray(),
        ];
    }
}
