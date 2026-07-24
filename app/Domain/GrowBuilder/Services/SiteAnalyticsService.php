<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Services;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use Illuminate\Support\Collection;

class SiteAnalyticsService
{
    public function getTotalViews(int $siteId, int $days): int
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->count();
    }

    public function getTotalVisitors(int $siteId, int $days): int
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->distinct('ip_address')
            ->count();
    }

    public function getPreviousPeriodViews(int $siteId, int $days): int
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days * 2))
            ->where('viewed_date', '<', now()->subDays($days))
            ->count();
    }

    public function getDailyStats(int $siteId, int $days): Collection
    {
        $query = GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->selectRaw('DATE(viewed_date) as date, COUNT(*) as views, COUNT(DISTINCT CASE WHEN ip_address IS NOT NULL AND ip_address != "" THEN ip_address END) as visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyStats = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $stats = $query->get($date);
            $dailyStats->push([
                'date' => $date,
                'views' => $stats ? (int) $stats->views : 0,
                'visitors' => $stats ? (int) $stats->visitors : 0,
            ]);
        }

        return $dailyStats;
    }

    public function getDeviceStats(int $siteId, int $days, int $totalViews): Collection
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->selectRaw('COALESCE(NULLIF(device_type, ""), "Unknown") as device, COUNT(*) as count')
            ->groupBy('device')
            ->get()
            ->map(fn($item) => [
                'device' => $item->device,
                'count' => (int) $item->count,
                'percentage' => $totalViews > 0 ? round(($item->count / $totalViews) * 100, 1) : 0,
            ]);
    }

    public function getTopPages(int $siteId, int $days, int $limit = 10): Collection
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->selectRaw('COALESCE(NULLIF(path, ""), "/") as path, COUNT(*) as views')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'path' => $item->path,
                'views' => (int) $item->views,
                'avgTime' => 0,
                'bounceRate' => 0,
            ]);
    }

    public function getTrafficSources(int $siteId, int $days, int $totalVisitors): Collection
    {
        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->selectRaw('
                CASE 
                    WHEN referrer IS NULL OR referrer = "" THEN "Direct"
                    WHEN referrer LIKE "%google%" THEN "Google"
                    WHEN referrer LIKE "%facebook%" THEN "Facebook"
                    WHEN referrer LIKE "%twitter%" THEN "Twitter"
                    ELSE "Other"
                END as source,
                COUNT(DISTINCT ip_address) as visitors
            ')
            ->groupBy('source')
            ->get()
            ->map(fn($item) => [
                'source' => $item->source,
                'visitors' => (int) $item->visitors,
                'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
                'type' => match(strtolower($item->source)) {
                    'direct' => 'direct',
                    'google' => 'search',
                    'facebook', 'twitter' => 'social',
                    default => 'referral',
                },
            ]);
    }

    public function getGeographicData(int $siteId, int $days, int $totalVisitors): Collection
    {
        $countryNames = [
            'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada',
            'DE' => 'Germany', 'FR' => 'France', 'AU' => 'Australia',
            'NL' => 'Netherlands', 'ZM' => 'Zambia', 'ZA' => 'South Africa',
            'KE' => 'Kenya', 'NG' => 'Nigeria', 'GH' => 'Ghana',
        ];

        return GrowBuilderPageView::where('site_id', $siteId)
            ->where('viewed_date', '>=', now()->subDays($days))
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->selectRaw('country, COUNT(DISTINCT ip_address) as visitors')
            ->groupBy('country')
            ->orderByDesc('visitors')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'country' => $countryNames[strtoupper($item->country)] ?? ucfirst(strtolower($item->country)),
                'countryCode' => strtoupper($item->country),
                'visitors' => (int) $item->visitors,
                'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
            ]);
    }
}
