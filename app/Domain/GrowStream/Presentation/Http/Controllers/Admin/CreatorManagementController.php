<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreatorManagementController extends Controller
{
    /**
     * Get all creators
     */
    public function index(Request $request): JsonResponse
    {
        $query = CreatorProfile::with('user')
            ->withCount([
                'videos',
                'videos as published_videos_count' => function ($q) {
                    $q->where('is_published', true);
                },
            ]);

        // Filter by verification status
        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $creators = $query->paginate($request->get('per_page', 20));

        // Add stats for each creator
        $creatorsWithStats = $creators->getCollection()->map(function ($creator) {
            $totalViews = VideoView::whereHas('video', function ($q) use ($creator) {
                $q->where('creator_id', $creator->user_id);
            })->count();

            $totalWatchTime = WatchHistory::whereHas('video', function ($q) use ($creator) {
                $q->where('creator_id', $creator->user_id);
            })->sum('watch_duration');

            $creator->stats = [
                'total_views' => $totalViews,
                'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
                'avg_views_per_video' => $creator->published_videos_count > 0 
                    ? round($totalViews / $creator->published_videos_count, 2) 
                    : 0,
            ];

            return $creator;
        });

        $creators->setCollection($creatorsWithStats);

        return response()->json([
            'success' => true,
            'data' => $creators->items(),
            'meta' => [
                'current_page' => $creators->currentPage(),
                'per_page' => $creators->perPage(),
                'total' => $creators->total(),
                'last_page' => $creators->lastPage(),
            ],
        ]);
    }

    /**
     * Get creator details
     */
    public function show(CreatorProfile $creator): JsonResponse
    {
        $creator->load(['user', 'videos' => function ($query) {
            $query->latest()->limit(10);
        }]);

        // Calculate detailed stats
        $totalViews = VideoView::whereHas('video', function ($q) use ($creator) {
            $q->where('creator_id', $creator->user_id);
        })->count();

        $uniqueViewers = VideoView::whereHas('video', function ($q) use ($creator) {
            $q->where('creator_id', $creator->user_id);
        })->distinct('user_id')->count('user_id');

        $totalWatchTime = WatchHistory::whereHas('video', function ($q) use ($creator) {
            $q->where('creator_id', $creator->user_id);
        })->sum('watch_duration');

        $avgWatchTime = WatchHistory::whereHas('video', function ($q) use ($creator) {
            $q->where('creator_id', $creator->user_id);
        })->avg('watch_duration');

        // Views over last 30 days
        $recentViews = VideoView::whereHas('video', function ($q) use ($creator) {
            $q->where('creator_id', $creator->user_id);
        })
        ->where('created_at', '>=', now()->subDays(30))
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as views')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Top performing videos
        $topVideos = $creator->videos()
            ->withCount('views')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'creator' => $creator,
                'stats' => [
                    'total_videos' => $creator->videos()->count(),
                    'published_videos' => $creator->videos()->where('is_published', true)->count(),
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'total_watch_time_hours' => round($totalWatchTime / 3600, 2),
                    'avg_watch_time_seconds' => round($avgWatchTime, 2),
                    'total_revenue' => $creator->total_revenue,
                    'pending_payout' => $creator->pending_payout,
                ],
                'recent_views' => $recentViews,
                'top_videos' => $topVideos,
            ],
        ]);
    }

    /**
     * Verify creator
     */
    public function verify(CreatorProfile $creator): JsonResponse
    {
        $creator->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $creator,
            'message' => 'Creator verified successfully',
        ]);
    }

    /**
     * Unverify creator
     */
    public function unverify(CreatorProfile $creator): JsonResponse
    {
        $creator->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $creator,
            'message' => 'Creator verification removed',
        ]);
    }

    /**
     * Suspend creator
     */
    public function suspend(Request $request, CreatorProfile $creator): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $creator->update([
            'is_active' => false,
            'suspension_reason' => $request->reason,
            'suspended_at' => now(),
        ]);

        // Optionally unpublish all creator's videos
        if ($request->boolean('unpublish_videos')) {
            $creator->videos()->update(['is_published' => false]);
        }

        return response()->json([
            'success' => true,
            'data' => $creator,
            'message' => 'Creator suspended successfully',
        ]);
    }

    /**
     * Unsuspend creator
     */
    public function unsuspend(CreatorProfile $creator): JsonResponse
    {
        $creator->update([
            'is_active' => true,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $creator,
            'message' => 'Creator unsuspended successfully',
        ]);
    }

    /**
     * Update creator limits
     */
    public function updateLimits(Request $request, CreatorProfile $creator): JsonResponse
    {
        $request->validate([
            'max_videos' => 'nullable|integer|min:0',
            'max_storage_gb' => 'nullable|integer|min:0',
            'max_upload_size_mb' => 'nullable|integer|min:0',
            'revenue_share_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $creator->update($request->only([
            'max_videos',
            'max_storage_gb',
            'max_upload_size_mb',
            'revenue_share_percentage',
        ]));

        return response()->json([
            'success' => true,
            'data' => $creator,
            'message' => 'Creator limits updated successfully',
        ]);
    }
}
