<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Infrastructure\GrowBuilder\Models\AIFeedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class GrowBuilderController extends Controller
{
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
            ->withQueryString();

        // Stats
        $stats = [
            'total' => GrowBuilderSite::where('status', '!=', 'deleted')->count(),
            'published' => GrowBuilderSite::where('status', 'published')->count(),
            'draft' => GrowBuilderSite::where('status', 'draft')->count(),
            'deleted' => GrowBuilderSite::where('status', 'deleted')->count(),
        ];

        return Inertia::render('Admin/GrowBuilder/Index', [
            'sites' => $sites,
            'stats' => $stats,
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

        return Inertia::render('Admin/GrowBuilder/Analytics', [
            'sitesOverTime' => $dates,
            'topSites' => $topSites,
            'aiStats' => $aiStats,
            'stats' => $stats,
        ]);
    }
}
