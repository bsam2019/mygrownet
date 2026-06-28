<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\AIFeedback;
use App\Services\GrowBuilder\StorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class GrowBuilderController extends Controller
{
    public function __construct(
        private StorageService $storageService,
        private \App\Domain\Module\Services\SubscriptionService $subscriptionService
    ) {}
    /**
     * Display all sites (admin view)
     */
    public function index(Request $request)
    {
        $query = GrowBuilderSite::with('user:id,name,email')
            ->where('status', '!=', 'deleted');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $sites = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($site) => [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
                'status' => $site->status,
                'plan' => $site->plan,
                'published_at' => $site->published_at,
                'created_at' => $site->created_at,
                'updated_at' => $site->updated_at,
                'user' => $site->user,
                'storage_used' => $site->storage_used,
                'storage_limit' => $site->storage_limit,
                'storage_used_formatted' => $site->storage_used_formatted,
                'storage_limit_formatted' => $site->storage_limit_formatted,
                'storage_percentage' => $site->storage_percentage,
            ])
            ->withQueryString();

        // Stats
        $stats = [
            'total' => GrowBuilderSite::where('status', '!=', 'deleted')->count(),
            'published' => GrowBuilderSite::where('status', 'published')->count(),
            'draft' => GrowBuilderSite::where('status', 'draft')->count(),
            'deleted' => GrowBuilderSite::where('status', 'deleted')->count(),
        ];

        // Storage stats
        $storageStats = $this->storageService->getGlobalStorageStats();

        return Inertia::render('Admin/GrowBuilder/Index', [
            'sites' => $sites,
            'stats' => $stats,
            'storageStats' => $storageStats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Display deleted sites (trash)
     */
    public function deleted(Request $request)
    {
        $query = GrowBuilderSite::with('user:id,name,email')
            ->where('status', 'deleted');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        $sites = $query->orderBy('scheduled_deletion_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/GrowBuilder/Deleted', [
            'sites' => $sites,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Restore a deleted site
     */
    public function restore(int $id)
    {
        $site = GrowBuilderSite::where('status', 'deleted')->findOrFail($id);

        $site->update([
            'status' => 'draft',
            'scheduled_deletion_at' => null,
            'deletion_reason' => null,
        ]);

        return back()->with('success', "Site '{$site->name}' has been restored.");
    }

    /**
     * Force delete a site immediately (permanent)
     */
    public function forceDelete(int $id)
    {
        $site = GrowBuilderSite::findOrFail($id);
        $siteName = $site->name;

        // Delete related data (check if relationships exist)
        if (method_exists($site, 'pages')) {
            $site->pages()->delete();
        }
        if (method_exists($site, 'media')) {
            $site->media()->delete();
        }
        if (method_exists($site, 'sitePosts')) {
            $site->sitePosts()->delete();
        }
        if (method_exists($site, 'siteUsers')) {
            $site->siteUsers()->delete();
        }
        if (method_exists($site, 'siteRoles')) {
            $site->siteRoles()->delete();
        }
        
        // Force delete the site (bypasses soft delete)
        $site->forceDelete();

        return back()->with('success', "Site '{$siteName}' has been permanently deleted.");
    }

    /**
     * Toggle site publish status
     */
    public function togglePublish(int $id)
    {
        $site = GrowBuilderSite::findOrFail($id);

        if ($site->status === 'published') {
            $site->update(['status' => 'draft', 'published_at' => null]);
            $message = "Site '{$site->name}' has been unpublished.";
        } else {
            $site->update(['status' => 'published', 'published_at' => now()]);
            $message = "Site '{$site->name}' has been published.";
        }

        return back()->with('success', $message);
    }

    /**
     * Display analytics overview
     */
    public function analytics()
    {
        // Sites created over time (last 30 days)
        $sitesOverTime = GrowBuilderSite::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates
        $dates = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = $sitesOverTime->firstWhere('date', $date)?->count ?? 0;
            $dates->push(['date' => $date, 'count' => $count]);
        }

        // Top sites by pages
        $topSites = GrowBuilderSite::withCount('pages')
            ->where('status', '!=', 'deleted')
            ->orderBy('pages_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'subdomain', 'status']);

        // AI usage stats
        $aiStats = [
            'total_requests' => AIFeedback::count(),
            'positive_feedback' => AIFeedback::where('applied', true)->count(),
            'negative_feedback' => AIFeedback::where('applied', false)->count(),
        ];

        // Overall stats
        $stats = [
            'total_sites' => GrowBuilderSite::count(),
            'active_sites' => GrowBuilderSite::where('status', 'published')->count(),
            'total_pages' => \DB::table('growbuilder_pages')->count(),
            'total_users' => \DB::table('site_users')->distinct('id')->count('id'),
        ];

        // Storage stats
        $storageStats = $this->storageService->getGlobalStorageStats();

        return Inertia::render('Admin/GrowBuilder/Analytics', [
            'sitesOverTime' => $dates,
            'topSites' => $topSites,
            'aiStats' => $aiStats,
            'stats' => $stats,
            'storageStats' => $storageStats,
        ]);
    }

    /**
     * Display storage management page
     */
    public function storage(Request $request)
    {
        $query = GrowBuilderSite::with(['user:id,name,email', 'user.roles'])
            ->where('status', '!=', 'deleted');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by storage status - we'll need to handle this differently since we're calculating limits dynamically
        // For now, we'll apply the filter after getting the data
        $storageStatusFilter = $request->get('storage_status');

        // Sort by storage
        $sortBy = $request->get('sort', 'storage_used');
        $sortDir = $request->get('dir', 'desc');
        
        if (in_array($sortBy, ['storage_used', 'storage_limit', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        }

        // Pre-fetch all user subscriptions to avoid N+1 queries
        $userIds = $query->pluck('user_id')->unique()->toArray();
        $userTiers = [];
        
        $subscriptions = \DB::table('module_subscriptions')
            ->whereIn('user_id', $userIds)
            ->where('module_id', 'growbuilder')
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->get()
            ->keyBy('user_id');
        
        foreach ($userIds as $userId) {
            if (isset($subscriptions[$userId])) {
                $userTiers[$userId] = $subscriptions[$userId]->subscription_tier;
            } else {
                $userTiers[$userId] = 'free';
            }
        }

        $sites = $query->paginate(20)
            ->through(function ($site) use ($userTiers) {
                // Get tier from pre-fetched data
                $currentTier = $site->user_id && isset($userTiers[$site->user_id]) 
                    ? $userTiers[$site->user_id] 
                    : 'free';
                    
                $subscriptionStorageLimit = $this->storageService->getStorageLimitForTier($currentTier);
                
                // For agency tier, show pooled storage info
                if ($currentTier === 'agency') {
                    // Get total usage across all user's sites
                    $totalUserUsage = \DB::table('growbuilder_sites')
                        ->where('user_id', $site->user_id)
                        ->where('status', '!=', 'deleted')
                        ->sum('storage_used');
                    
                    $storagePercentage = $subscriptionStorageLimit > 0 
                        ? min(100, round(($totalUserUsage / $subscriptionStorageLimit) * 100, 1))
                        : 0;
                    
                    $isOverLimit = $totalUserUsage > $subscriptionStorageLimit;
                    $isNearLimit = $subscriptionStorageLimit > 0 && $totalUserUsage >= ($subscriptionStorageLimit * 0.8) && $totalUserUsage <= $subscriptionStorageLimit;
                    
                    return [
                        'id' => $site->id,
                        'name' => $site->name,
                        'subdomain' => $site->subdomain,
                        'status' => $site->status,
                        'plan' => $currentTier,
                        'user' => $site->user,
                        'storage_used' => $site->storage_used,
                        'storage_limit' => $subscriptionStorageLimit,
                        'storage_used_formatted' => $this->storageService->formatBytes($site->storage_used),
                        'storage_limit_formatted' => $this->storageService->formatBytes($subscriptionStorageLimit) . ' (pooled)',
                        'storage_percentage' => $storagePercentage,
                        'storage_calculated_at' => $site->storage_calculated_at,
                        'is_over_limit' => $isOverLimit,
                        'is_near_limit' => $isNearLimit,
                        'is_pooled' => true,
                        'total_user_usage' => $totalUserUsage,
                        'total_user_usage_formatted' => $this->storageService->formatBytes($totalUserUsage),
                    ];
                }
                
                // For non-agency tiers, show individual site storage
                $storagePercentage = $subscriptionStorageLimit > 0 
                    ? min(100, round(($site->storage_used / $subscriptionStorageLimit) * 100, 1))
                    : 0;
                
                $isOverLimit = $site->storage_used > $subscriptionStorageLimit;
                $isNearLimit = $subscriptionStorageLimit > 0 && $site->storage_used >= ($subscriptionStorageLimit * 0.8) && $site->storage_used <= $subscriptionStorageLimit;
                
                return [
                    'id' => $site->id,
                    'name' => $site->name,
                    'subdomain' => $site->subdomain,
                    'status' => $site->status,
                    'plan' => $currentTier,
                    'user' => $site->user,
                    'storage_used' => $site->storage_used,
                    'storage_limit' => $subscriptionStorageLimit,
                    'storage_used_formatted' => $this->storageService->formatBytes($site->storage_used),
                    'storage_limit_formatted' => $this->storageService->formatBytes($subscriptionStorageLimit),
                    'storage_percentage' => $storagePercentage,
                    'storage_calculated_at' => $site->storage_calculated_at,
                    'is_over_limit' => $isOverLimit,
                    'is_near_limit' => $isNearLimit,
                    'is_pooled' => false,
                ];
            });

        // Apply storage status filter after calculating limits
        if ($storageStatusFilter) {
            $sites->getCollection()->transform(function ($sitesCollection) use ($storageStatusFilter) {
                return $sitesCollection->filter(function ($site) use ($storageStatusFilter) {
                    switch ($storageStatusFilter) {
                        case 'over':
                            return $site['is_over_limit'];
                        case 'near':
                            return $site['is_near_limit'];
                        case 'normal':
                            return !$site['is_over_limit'] && !$site['is_near_limit'];
                        default:
                            return true;
                    }
                });
            });
        }

        $sites = $sites->withQueryString();

        $storageStats = $this->storageService->getGlobalStorageStats();
        
        // Recalculate total allocated based on subscription tiers using the same pre-fetched data
        $totalAllocatedFromSubscriptions = 0;
        foreach ($userTiers as $tier) {
            $totalAllocatedFromSubscriptions += $this->storageService->getStorageLimitForTier($tier);
        }
        
        // Override the total_allocated with subscription-based calculation
        $storageStats['total_allocated'] = $totalAllocatedFromSubscriptions;
        $storageStats['total_allocated_formatted'] = $this->storageService->formatBytes($totalAllocatedFromSubscriptions);
        
        $tierLimits = $this->storageService->getAllTierLimits();
        
        // Convert to format expected by frontend (plan => bytes)
        $planLimits = [];
        foreach ($tierLimits as $tier => $limit) {
            $planLimits[$tier] = $limit['bytes'];
        }

        return Inertia::render('Admin/GrowBuilder/Storage', [
            'sites' => $sites,
            'storageStats' => $storageStats,
            'planLimits' => $planLimits,
            'filters' => $request->only(['search', 'storage_status', 'sort', 'dir']),
        ]);
    }

    /**
     * Recalculate storage for a site
     */
    public function recalculateStorage(int $id)
    {
        $site = GrowBuilderSite::findOrFail($id);
        $this->storageService->updateStorageUsage($site);

        return back()->with('success', "Storage recalculated for '{$site->name}'.");
    }

    /**
     * Recalculate storage for all sites
     */
    public function recalculateAllStorage()
    {
        $count = $this->storageService->recalculateAllSites();

        return back()->with('success', "Storage recalculated for {$count} sites.");
    }
}
