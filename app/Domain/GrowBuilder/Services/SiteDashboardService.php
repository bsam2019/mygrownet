<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Services;

use App\Domain\GrowBuilder\Repositories\SiteRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\SiteContactMessage;
use Illuminate\Support\Collection;

class SiteDashboardService
{
    public function __construct(
        private SiteRepositoryInterface $siteRepository,
    ) {}

    public function getUserSitesData(int $userId): Collection
    {
        return GrowBuilderSite::byUser($userId)
            ->with(['pages', 'media', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getDashboardStats(Collection $sites): array
    {
        $siteIds = $sites->pluck('id');

        $totalPageViews = GrowBuilderPageView::whereIn('site_id', $siteIds)->count();
        $totalMessages = SiteContactMessage::whereIn('site_id', $siteIds)->count();
        $unreadMessages = SiteContactMessage::whereIn('site_id', $siteIds)->where('status', 'unread')->count();

        return [
            'totalSites' => $sites->count(),
            'publishedSites' => $sites->where('status', 'published')->count(),
            'totalPageViews' => $totalPageViews,
            'totalOrders' => 0,
            'totalRevenue' => 0,
            'totalMessages' => $totalMessages,
            'unreadMessages' => $unreadMessages,
        ];
    }

    public function getPageViewsPerSite(Collection $sites): array
    {
        $siteIds = $sites->pluck('id');

        return GrowBuilderPageView::whereIn('site_id', $siteIds)
            ->selectRaw('site_id, COUNT(*) as views')
            ->groupBy('site_id')
            ->pluck('views', 'site_id')
            ->toArray();
    }

    public function getMessageCounts(Collection $sites): Collection
    {
        $siteIds = $sites->pluck('id');

        return SiteContactMessage::whereIn('site_id', $siteIds)
            ->selectRaw('site_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as unread', ['unread'])
            ->groupBy('site_id')
            ->get()
            ->keyBy('site_id');
    }

    public function getDailyPageViews(Collection $sites, int $days = 14): Collection
    {
        $siteIds = $sites->pluck('id');
        $startDate = now()->subDays($days - 1)->startOfDay();

        return GrowBuilderPageView::whereIn('site_id', $siteIds)
            ->where('viewed_date', '>=', $startDate)
            ->selectRaw('site_id, viewed_date, COUNT(*) as views')
            ->groupBy('site_id', 'viewed_date')
            ->orderBy('site_id')
            ->orderBy('viewed_date')
            ->get()
            ->groupBy('site_id')
            ->map(fn($rows) => $rows->pluck('views', 'viewed_date')->toArray());
    }

    public function getRecentMessages(Collection $sites, int $limit = 5): Collection
    {
        $siteIds = $sites->pluck('id');

        return SiteContactMessage::whereIn('site_id', $siteIds)
            ->where('status', 'unread')
            ->with('site')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'siteId' => $msg->site_id,
                'siteName' => $msg->site->name ?? 'Unknown',
                'siteSubdomain' => $msg->site->subdomain ?? '',
                'name' => $msg->name,
                'email' => $msg->email,
                'subject' => $msg->subject,
                'message' => $msg->message,
                'status' => $msg->status,
                'createdAt' => $msg->created_at->diffForHumans(),
            ]);
    }

    public function formatSiteData($site, array $pageViewsPerSite, Collection $messageCounts, Collection $rawDailyViews, int $days = 14): array
    {
        $siteDaily = $rawDailyViews[$site->id] ?? [];
        $sparkline = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $sparkline[] = (int)($siteDaily[$date] ?? 0);
        }

        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'customDomain' => $site->custom_domain,
            'description' => $site->description,
            'status' => $site->status,
            'url' => $site->url,
            'logo' => $site->logo,
            'favicon' => $site->favicon,
            'isPublished' => $site->isPublished(),
            'onboardingCompleted' => $site->onboarding_completed,
            'createdAt' => $site->created_at->diffForHumans(),
            'updatedAt' => $site->updated_at->diffForHumans(),
            'publishedAt' => $site->published_at?->diffForHumans(),
            'storageUsed' => $site->storage_used ?? 0,
            'storageLimit' => $site->storage_limit ?? 104857600,
            'storageUsedFormatted' => $site->storage_used_formatted,
            'storageLimitFormatted' => $site->storage_limit_formatted,
            'storagePercentage' => $site->storage_percentage,
            'pageCount' => $site->pages->count(),
            'mediaCount' => $site->media->count(),
            'pageViews' => $pageViewsPerSite[$site->id] ?? 0,
            'ordersCount' => 0,
            'revenue' => 0,
            'messagesCount' => $messageCounts[$site->id]->total ?? 0,
            'unreadMessages' => $messageCounts[$site->id]->unread ?? 0,
            'sparkline' => $sparkline,
            'client' => $site->client ? [
                'id' => $site->client->id,
                'name' => $site->client->client_name,
                'company' => $site->client->company_name,
                'type' => $site->client->client_type,
            ] : null,
        ];
    }

    public function getSiteWithAccessCheck(int $siteId, int $userId)
    {
        return GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $userId)
            ->with(['pages', 'media', 'client'])
            ->firstOrFail();
    }

    public function findSiteForUser(int $siteId, int $userId): ?GrowBuilderSite
    {
        return GrowBuilderSite::where('id', $siteId)
            ->where('user_id', $userId)
            ->first();
    }
}
