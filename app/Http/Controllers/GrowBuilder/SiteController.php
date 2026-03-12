<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct()
    {
        // Dependencies commented out for now
    }

    // DEBUG: Simple test method
    public function test(Request $request)
    {
        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => [],
            'stats' => ['totalSites' => 0],
            'subscription' => ['tier' => 'free'],
            'modules' => [],
        ]);
    }
    
    public function index(Request $request)
    {
        // DEBUG: Log that we're hitting this method
        \Log::info('GrowBuilder SiteController@index hit', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'is_admin' => $request->user()?->hasRole(['Administrator', 'admin', 'superadmin']),
            'url' => $request->url(),
            'route_name' => $request->route()?->getName(),
            'middleware' => $request->route()?->middleware(),
        ]);

        $user = $request->user();
        
        // Get user's sites
        $sites = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::byUser($user->id)
            ->with(['pages', 'media'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get basic stats with actual analytics data
        $totalPageViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::whereIn('site_id', $sites->pluck('id'))->count();
        $totalOrders = 0; // TODO: Add when orders model is available
        $totalRevenue = 0; // TODO: Add when orders model is available
        $totalMessages = 0; // TODO: Add when contact messages model is available
        $unreadMessages = 0; // TODO: Add when contact messages model is available

        $stats = [
            'totalSites' => $sites->count(),
            'publishedSites' => $sites->where('status', 'published')->count(),
            'totalPageViews' => $totalPageViews,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'totalMessages' => $totalMessages,
            'unreadMessages' => $unreadMessages,
        ];

        // Get page views per site for individual site stats
        $pageViewsPerSite = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::whereIn('site_id', $sites->pluck('id'))
            ->selectRaw('site_id, COUNT(*) as views')
            ->groupBy('site_id')
            ->pluck('views', 'site_id')
            ->toArray();

        // Format sites for frontend
        $sitesData = $sites->map(function ($site) use ($pageViewsPerSite) {
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
                'storageLimit' => $site->storage_limit ?? 104857600, // 100MB default
                'storageUsedFormatted' => $site->storage_used_formatted,
                'storageLimitFormatted' => $site->storage_limit_formatted,
                'storagePercentage' => $site->storage_percentage,
                'pageCount' => $site->pages->count(),
                'mediaCount' => $site->media->count(),
                'pageViews' => $pageViewsPerSite[$site->id] ?? 0,
                'ordersCount' => 0, // TODO: Get actual orders
                'revenue' => 0, // TODO: Get actual revenue
                'messagesCount' => 0, // TODO: Get actual messages
                'unreadMessages' => 0, // TODO: Get unread messages
            ];
        });

        // Basic subscription info (simplified for now)
        $subscription = [
            'tier' => 'free',
            'tierName' => 'Free',
            'sitesLimit' => 3,
            'sitesUsed' => $sites->count(),
            'canCreateSite' => $sites->count() < 3,
            'expiresAt' => null,
        ];

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => $sitesData,
            'stats' => $stats,
            'subscription' => $subscription,
            'modules' => [],
        ]);
    }

    // Placeholder methods to prevent route errors
    public function create(Request $request)
    {
        return response()->json(['message' => 'Create method - under development']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Store method - under development']);
    }

    public function show(Request $request, int $id)
    {
        return response()->json(['message' => 'Show method - under development']);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(['message' => 'Update method - under development']);
    }

    public function destroy(Request $request, int $id)
    {
        return response()->json(['message' => 'Destroy method - under development']);
    }

    public function publish(Request $request, int $id)
    {
        return response()->json(['message' => 'Publish method - under development']);
    }

    public function unpublish(Request $request, int $id)
    {
        return response()->json(['message' => 'Unpublish method - under development']);
    }

    public function settings(Request $request, int $id)
    {
        return response()->json(['message' => 'Settings method - under development']);
    }

    public function analytics(Request $request, int $id)
        {
            $user = $request->user();

            // Get the site
            $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['pages'])
                ->firstOrFail();

            // Get period from request (default 30 days)
            $period = $request->get('period', '30d');
            $days = match($period) {
                '7d' => 7,
                '30d' => 30,
                '90d' => 90,
                default => 30,
            };

            // Get total views and visitors (only count records with IP addresses for visitors)
            $totalViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->count();

            $totalVisitors = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->whereNotNull('ip_address')
                ->where('ip_address', '!=', '')
                ->distinct('ip_address')
                ->count();

            // Calculate change from previous period
            $previousPeriodViews = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days * 2))
                ->where('viewed_date', '<', now()->subDays($days))
                ->count();

            $viewsChange = $previousPeriodViews > 0 
                ? round((($totalViews - $previousPeriodViews) / $previousPeriodViews) * 100, 1)
                : 0;

            // Get daily stats - fill missing dates with zeros for proper trend line
            $dailyStatsQuery = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('DATE(viewed_date) as date, COUNT(*) as views, COUNT(DISTINCT CASE WHEN ip_address IS NOT NULL AND ip_address != "" THEN ip_address END) as visitors')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Create complete date range with zeros for missing dates
            $dailyStats = collect();
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $stats = $dailyStatsQuery->get($date);

                $dailyStats->push([
                    'date' => $date,
                    'views' => $stats ? (int) $stats->views : 0,
                    'visitors' => $stats ? (int) $stats->visitors : 0,
                ]);
            }

            // Get device stats - only real data
            $deviceStats = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('COALESCE(NULLIF(device_type, ""), "Unknown") as device, COUNT(*) as count')
                ->groupBy('device')
                ->get()
                ->map(function ($item) use ($totalViews) {
                    return [
                        'device' => $item->device,
                        'count' => (int) $item->count,
                        'percentage' => $totalViews > 0 ? round(($item->count / $totalViews) * 100, 1) : 0,
                    ];
                });

            // Get top pages - only real data
            $topPages = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
                ->where('viewed_date', '>=', now()->subDays($days))
                ->selectRaw('COALESCE(NULLIF(path, ""), "/") as path, COUNT(*) as views')
                ->groupBy('path')
                ->orderByDesc('views')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'path' => $item->path,
                        'views' => (int) $item->views,
                        'avgTime' => 0, // We don't track this yet
                        'bounceRate' => 0, // We don't track this yet
                    ];
                });

            // Get traffic sources - only real data
            $trafficSources = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
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
                ->map(function ($item) use ($totalVisitors) {
                    $type = match(strtolower($item->source)) {
                        'direct' => 'direct',
                        'google' => 'search',
                        'facebook', 'twitter' => 'social',
                        default => 'referral',
                    };

                    return [
                        'source' => $item->source,
                        'visitors' => (int) $item->visitors,
                        'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
                        'type' => $type,
                    ];
                });

            // Get geographic data - only real data, only count records with IP addresses
            $geographicData = \App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView::where('site_id', $id)
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
                ->map(function ($item) use ($totalVisitors) {
                    // Map country codes to proper names (basic mapping)
                    $countryNames = [
                        'US' => 'United States',
                        'GB' => 'United Kingdom', 
                        'CA' => 'Canada',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'AU' => 'Australia',
                        'NL' => 'Netherlands',
                        'ZM' => 'Zambia',
                        'ZA' => 'South Africa',
                        'KE' => 'Kenya',
                        'NG' => 'Nigeria',
                        'GH' => 'Ghana',
                    ];

                    $countryCode = strtoupper($item->country);
                    $countryName = $countryNames[$countryCode] ?? ucfirst(strtolower($item->country));

                    return [
                        'country' => $countryName,
                        'countryCode' => $countryCode,
                        'visitors' => (int) $item->visitors,
                        'percentage' => $totalVisitors > 0 ? round(($item->visitors / $totalVisitors) * 100, 1) : 0,
                    ];
                });

            // Calculate session metrics from available data
            $avgSessionDuration = 0; // We don't track session duration yet
            $newVisitorsCount = $totalVisitors; // Assume all are new since we don't track returning visitors yet
            $returningVisitorsCount = 0;

            return Inertia::render('GrowBuilder/Sites/Analytics', [
                'site' => [
                    'id' => $site->id,
                    'name' => $site->name,
                    'subdomain' => $site->subdomain,
                ],
                'totalViews' => $totalViews,
                'totalVisitors' => $totalVisitors,
                'viewsChange' => $viewsChange,
                'avgSessionDuration' => $avgSessionDuration,
                'newVisitors' => $newVisitorsCount,
                'returningVisitors' => $returningVisitorsCount,
                'dailyStats' => $dailyStats,
                'deviceStats' => $deviceStats,
                'topPages' => $topPages,
                'trafficSources' => $trafficSources,
                'geographicData' => $geographicData,
                'conversionGoals' => [], // TODO: Add conversion tracking
                'period' => $period,
            ]);
        }

    public function exportAnalytics(Request $request, int $id)
    {
        return response()->json(['message' => 'Export analytics method - under development']);
    }

    public function messages(Request $request, int $id)
    {
        return response()->json(['message' => 'Messages method - under development']);
    }

    public function showMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Show message method - under development']);
    }

    public function replyMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Reply message method - under development']);
    }

    public function updateMessageStatus(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Update message status method - under development']);
    }

    public function deleteMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Delete message method - under development']);
    }

    public function exportMessages(Request $request, int $id)
    {
        return response()->json(['message' => 'Export messages method - under development']);
    }

    public function completeOnboarding(Request $request, int $id)
    {
        return response()->json(['message' => 'Complete onboarding method - under development']);
    }

    public function users(Request $request, int $id)
    {
        return response()->json(['message' => 'Users method - under development']);
    }

    public function createUser(Request $request, int $id)
    {
        return response()->json(['message' => 'Create user method - under development']);
    }

    public function updateUserRole(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Update user role method - under development']);
    }

    public function deleteUser(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Delete user method - under development']);
    }

    public function roles(Request $request, int $id)
    {
        return response()->json(['message' => 'Roles method - under development']);
    }

    public function permissions(Request $request, int $id)
    {
        return response()->json(['message' => 'Permissions method - under development']);
    }

    public function createRole(Request $request, int $id)
    {
        return response()->json(['message' => 'Create role method - under development']);
    }

    public function updateRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Update role method - under development']);
    }

    public function deleteRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Delete role method - under development']);
    }

    public function restore(Request $request, int $id)
    {
        return response()->json(['message' => 'Restore method - under development']);
    }

    public function preview(Request $request, string $subdomain, ?string $page = null)
    {
        return response()->json(['message' => 'Preview method - under development']);
    }

    public function switchTier(Request $request)
    {
        return response()->json(['message' => 'Switch tier method - under development']);
    }

    public function showProduct(Request $request, string $subdomain, string $slug)
    {
        return response()->json(['message' => 'Show product method - under development']);
    }

    public function checkout(Request $request, string $subdomain)
    {
        return response()->json(['message' => 'Checkout method - under development']);
    }
}