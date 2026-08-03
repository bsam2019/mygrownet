<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\Core\Contracts\MyGrowIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrowStreamWebController
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private VideoSeriesRepositoryInterface $seriesRepo,
        private MyGrowIdentity $identity,
    ) {}

    public function redirectToLogin(): RedirectResponse
    {
        $returnUrl = route('growstream.home');

        return redirect()->away($this->identity->redirectToLogin($returnUrl));
    }

    public function redirectToRegister(): RedirectResponse
    {
        $returnUrl = route('growstream.home');
        $registerUrl = config('platform.identity.register_url', 'https://auth.mygrownet.com/register');
        $expires = time() + config('platform.identity.return_url_ttl', 300);
        $signature = hash_hmac('sha256', $returnUrl . '|' . $expires, config('platform.identity.signing_key') ?? '');

        return redirect()->away(
            $registerUrl
            . '?return_url=' . urlencode($returnUrl)
            . '&expires=' . $expires
            . '&signature=' . $signature
        );
    }

    public function home(): Response
    {
        $featured = $this->videoRepo->featured(10, ['creator.user', 'categories']);

        $trending = $this->videoRepo->query()
            ->published()
            ->with(['creator.user', 'categories'])
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get();

        $recent = $this->videoRepo->query()
            ->published()
            ->with(['creator.user', 'categories'])
            ->latest('published_at')
            ->take(10)
            ->get();

        $categories = VideoCategory::whereNull('parent_id')
            ->withCount('videos')
            ->orderBy('name')
            ->take(8)
            ->get();

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

    public function browse(Request $request): Response
    {
        $query = $this->videoRepo->query()->published()->with(['creator.user', 'categories']);

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

    public function videoDetail(string $slug): Response
    {
        $video = $this->videoRepo->findBySlug($slug);
        if (!$video) {
            abort(404);
        }

        $video->load(['creator.user', 'categories', 'tags', 'series']);

        $related = $this->videoRepo->query()
            ->published()
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

    public function seriesDetail(string $slug): Response
    {
        $series = $this->seriesRepo->findBySlug($slug);
        if (!$series) {
            abort(404);
        }

        $series->load(['creator.user']);

        $episodes = $this->videoRepo->query()
            ->published()
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

    public function search(Request $request): Response
    {
        $term = trim((string) $request->get('q', ''));

        $trending = $this->videoRepo->query()
            ->published()
            ->with(['creator.user', 'categories'])
            ->orderBy('view_count', 'desc')
            ->take(8)
            ->get();

        if ($term === '') {
            return Inertia::render('GrowStream/Search', [
                'query' => '',
                'videos' => [],
                'creators' => [],
                'categories' => [],
                'trending' => $trending,
            ]);
        }

        $videos = $this->videoRepo->query()
            ->published()
            ->with(['creator.user', 'categories'])
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('tags', fn ($tq) => $tq->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('categories', fn ($cq) => $cq->where('name', 'like', "%{$term}%"));
            })
            ->orderBy('view_count', 'desc')
            ->take(24)
            ->get();

        $creators = CreatorProfile::where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('display_name', 'like', "%{$term}%")
                  ->orWhere('channel_name', 'like', "%{$term}%")
                  ->orWhere('bio', 'like', "%{$term}%");
            })
            ->with('user')
            ->take(10)
            ->get();

        $categories = VideoCategory::where('is_active', true)
            ->where('name', 'like', "%{$term}%")
            ->withCount('videos')
            ->orderBy('name')
            ->take(8)
            ->get();

        return Inertia::render('GrowStream/Search', [
            'query' => $term,
            'videos' => $videos,
            'creators' => $creators,
            'categories' => $categories,
            'trending' => $trending,
        ]);
    }

    public function creatorProfile(string $slug): Response
    {
        $creator = CreatorProfile::with('user')
            ->where('is_active', true)
            ->where(function ($q) use ($slug) {
                $q->where('id', (int) $slug)
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', str_replace('-', ' ', $slug)));
            })
            ->first();

        if (!$creator) {
            abort(404);
        }

        $videos = $this->videoRepo->query()
            ->published()
            ->where('creator_id', $creator->id)
            ->with(['categories', 'tags'])
            ->latest('published_at')
            ->take(24)
            ->get();

        $series = $this->seriesRepo->query()
            ->where('creator_id', $creator->id)
            ->where('is_published', true)
            ->with('videos')
            ->latest()
            ->take(12)
            ->get();

        return Inertia::render('GrowStream/CreatorProfile', [
            'creator' => $creator,
            'videos' => $videos,
            'series' => $series,
        ]);
    }

    public function myVideos(Request $request): Response
    {
        $userId = auth()->id();

        $continueWatching = WatchHistory::where('user_id', $userId)
            ->where('is_completed', false)
            ->with(['video.creator.user', 'video.categories'])
            ->latest('last_watched_at')
            ->take(12)
            ->get()
            ->pluck('video')
            ->toArray();

        $watchlist = Watchlist::where('user_id', $userId)
            ->where('watchlistable_type', Video::class)
            ->with(['watchlistable.creator.user', 'watchlistable.categories'])
            ->latest('added_at')
            ->take(12)
            ->get()
            ->pluck('watchlistable')
            ->toArray();

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

    public function adminVideos(Request $request): Response
    {
        $query = $this->videoRepo->query()->with(['creator.user', 'categories']);

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

    public function adminVideoEdit(int $id): Response
    {
        $video = $this->videoRepo->findById($id);
        if (!$video) {
            abort(404);
        }

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

    public function adminAnalytics(Request $request): Response
    {
        return Inertia::render('GrowStream/Admin/Analytics');
    }

    public function adminCreators(Request $request): Response
    {
        $query = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile::with('user');

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
