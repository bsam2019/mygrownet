<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrowStreamWebController
{
    /**
     * Display the GrowStream home page
     */
    public function home(): Response
    {
        $featured = Video::published()
            ->where('is_featured', true)
            ->with(['creator.user', 'categories'])
            ->latest('featured_at')
            ->take(10)
            ->get();

        $trending = Video::published()
            ->with(['creator.user', 'categories'])
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get();

        $recent = Video::published()
            ->with(['creator.user', 'categories'])
            ->latest('published_at')
            ->take(10)
            ->get();

        $categories = VideoCategory::whereNull('parent_id')
            ->withCount('videos')
            ->orderBy('name')
            ->take(8)
            ->get();

        // Get continue watching for authenticated users
        $continueWatching = [];
        if (auth()->check()) {
            $continueWatching = WatchHistory::where('user_id', auth()->id())
                ->where('is_completed', false)
                ->with(['video.creator.user', 'video.categories'])
                ->latest('last_watched_at')
                ->take(6)
                ->get()
                ->toArray();
        }

        return Inertia::render('GrowStream/Home', [
            'featuredVideos' => $featured,
            'trendingVideos' => $trending,
            'recentVideos' => $recent,
            'categories' => $categories,
            'continueWatching' => $continueWatching,
        ]);
    }

    /**
     * Display the browse page with filters
     */
    public function browse(Request $request): Response
    {
        $query = Video::published()->with(['creator.user', 'categories']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->content_type);
        }

        if ($request->filled('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'trending':
                $query->orderBy('view_count', 'desc')
                    ->where('published_at', '>=', now()->subDays(7));
                break;
            case 'latest':
            default:
                $query->latest('published_at');
                break;
        }

        $videos = $query->paginate(24);

        $categories = VideoCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return Inertia::render('GrowStream/Browse', [
            'videos' => $videos,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'content_type', 'access_level', 'sort_by']),
        ]);
    }

    /**
     * Display video detail page
     */
    public function videoDetail(string $slug): Response
    {
        $video = Video::published()
            ->where('slug', $slug)
            ->with(['creator.user', 'categories', 'tags', 'series'])
            ->firstOrFail();

        // Get related videos
        $related = Video::published()
            ->where('id', '!=', $video->id)
            ->where(function ($query) use ($video) {
                $query->whereHas('categories', function ($q) use ($video) {
                    $q->whereIn('video_categories.id', $video->categories->pluck('id'));
                })
                    ->orWhereHas('tags', function ($q) use ($video) {
                        $q->whereIn('video_tags.id', $video->tags->pluck('id'));
                    });
            })
            ->with(['creator.user', 'categories'])
            ->take(12)
            ->get();

        // Get watch progress if authenticated
        $watchProgress = null;
        if (auth()->check()) {
            $watchProgress = WatchHistory::where('user_id', auth()->id())
                ->where('video_id', $video->id)
                ->first();
        }

        return Inertia::render('GrowStream/VideoDetail', [
            'video' => $video,
            'related' => $related,
            'watchProgress' => $watchProgress,
        ]);
    }

    /**
     * Display series detail page
     */
    public function seriesDetail(string $slug): Response
    {
        $series = VideoSeries::published()
            ->where('slug', $slug)
            ->with(['creator.user'])
            ->firstOrFail();

        // Get episodes grouped by season
        $episodes = Video::published()
            ->where('series_id', $series->id)
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->with(['creator.user'])
            ->get()
            ->groupBy('season_number');

        return Inertia::render('GrowStream/SeriesDetail', [
            'series' => $series,
            'episodes' => $episodes,
        ]);
    }

    /**
     * Display user's videos page (Continue Watching, Watchlist, History)
     */
    public function myVideos(Request $request): Response
    {
        $userId = auth()->id();

        // Continue watching (incomplete videos)
        $continueWatching = WatchHistory::where('user_id', $userId)
            ->where('is_completed', false)
            ->with(['video.creator.user', 'video.categories'])
            ->latest('last_watched_at')
            ->take(12)
            ->get()
            ->pluck('video')
            ->toArray();

        // Watchlist
        $watchlist = Watchlist::where('user_id', $userId)
            ->where('watchlistable_type', Video::class)
            ->with(['watchlistable.creator.user', 'watchlistable.categories'])
            ->latest('added_at')
            ->take(12)
            ->get()
            ->pluck('watchlistable')
            ->toArray();

        // Watch history (all videos)
        $history = WatchHistory::where('user_id', $userId)
            ->with(['video.creator.user', 'video.categories'])
            ->latest('last_watched_at')
            ->paginate(24);

        return Inertia::render('GrowStream/MyVideos', [
            'continueWatching' => $continueWatching,
            'watchlist' => $watchlist,
            'history' => $history,
        ]);
    }

    /**
     * Display admin videos management page
     */
    public function adminVideos(Request $request): Response
    {
        $query = Video::with(['creator.user', 'categories']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('upload_status', $request->status);
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->is_published === 'true');
        }

        $videos = $query->latest()->paginate(20);

        return Inertia::render('GrowStream/Admin/Videos', [
            'videos' => $videos,
        ]);
    }

    /**
     * Display admin video edit page
     */
    public function adminVideoEdit(Video $video): Response
    {
        $video->load(['creator.user', 'categories', 'tags', 'series']);

        $categories = VideoCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return Inertia::render('GrowStream/Admin/VideoEdit', [
            'video' => $video,
            'categories' => $categories,
        ]);
    }

    /**
     * Display admin analytics page
     */
    public function adminAnalytics(Request $request): Response
    {
        // This data would come from the API controller
        // For now, just render the page and let the frontend fetch data
        return Inertia::render('GrowStream/Admin/Analytics');
    }

    /**
     * Display admin creators management page
     */
    public function adminCreators(Request $request): Response
    {
        $query = CreatorProfile::with('user');

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('display_name', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === 'true');
        }

        $creators = $query->latest()->paginate(20);

        return Inertia::render('GrowStream/Admin/Creators', [
            'creators' => $creators,
        ]);
    }
}
