<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Web;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoCategory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\Core\Contracts\MyGrowIdentity;
use App\Domain\GrowStream\Services\AccessControlService;
use App\Domain\GrowStream\Services\AttributionService;
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
        private ?AttributionService $attribution = null,
        private ?AccessControlService $accessControl = null,
    ) {}

    public function redirectToLogin(Request $request): RedirectResponse
    {
        $returnUrl = $this->resolveReturnUrl($request);

        return redirect()->away($this->identity->redirectToLogin($returnUrl));
    }

    public function redirectToRegister(Request $request): RedirectResponse
    {
        $returnUrl = $this->resolveReturnUrl($request);
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

    /**
     * Determine where the user returns after identity login/register.
     * Prefers the page they were on (passed as ?redirect=), validated to the
     * GrowStream host to prevent open-redirect; falls back to the GrowStream home.
     */
    private function resolveReturnUrl(Request $request): string
    {
        $fallback = route('growstream.home');

        $redirect = $request->query('redirect');
        if (!is_string($redirect) || $redirect === '') {
            return $fallback;
        }

        $parts = parse_url($redirect);
        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? null;

        if ($host !== null) {
            $allowed = config('platform.identity.allowed_return_hosts', ['*.mygrownet.com']);
            $hostOk = false;
            foreach ($allowed as $pattern) {
                if (fnmatch($pattern, $host)) {
                    $hostOk = true;
                    break;
                }
            }
            if (!$hostOk) {
                return $fallback;
            }
        }

        // Only allow paths relative to GrowStream's own surface, never the root.
        if ($path === null || $path === '' || $path === '/') {
            return $fallback;
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return url($path . $query);
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
            'videos' => $this->paginateShape($videos),
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
        $watchlistItem = null;
        if (auth()->check()) {
            $watchProgress = WatchHistory::where('user_id', auth()->id())
                ->where('video_id', $video->id)
                ->first();
            $watchlistItem = Watchlist::where('user_id', auth()->id())
                ->where('watchlistable_type', Video::class)
                ->where('watchlistable_id', $video->id)
                ->first();
        }

        $userCanAccess = $this->canWatchVideo($video, $watchProgress);

        return Inertia::render('GrowStream/VideoDetail', [
            'video' => $video,
            'relatedVideos' => $related,
            'watchProgress' => $watchProgress,
            'watchlistItem' => $watchlistItem,
            'userCanAccess' => $userCanAccess,
            'throttled' => auth()->check() && $this->accessControl?->isThrottled(auth()->user()),
        ]);
    }

    /**
     * Server-side access check for a video (subscription tier + free-episode rule).
     */
    private function canWatchVideo($video, $watchProgress = null): bool
    {
        // Free-first-episode / free-access-level rule
        if ($video->access_level === 'free') {
            return true;
        }

        // Free episode within its series (episode_number <= free_episode_count)
        $episodeNumber = (int) ($video->episode_number ?? 0);
        if ($episodeNumber >= 1 && $video->series_id) {
            $series = $this->seriesRepo->findById($video->series_id);
            if ($series && $episodeNumber <= (int) ($series->free_episode_count ?? 1)) {
                return true;
            }
        }

        $user = auth()->user();
        if ($user === null || $this->accessControl === null) {
            return false;
        }

        return $this->accessControl->hasPaidSubscription($user)
            && $this->accessControl->remainingWatchMinutes($user) !== 0;
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
            ->get();

        $series->setRelation('videos', $episodes);

        return Inertia::render('GrowStream/SeriesDetail', [
            'series' => $series,
            'episodes' => $episodes->groupBy('season_number'),
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
                'series' => [],
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

        $series = VideoSeries::where('is_published', true)
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->with('creator')
            ->orderBy('title')
            ->take(12)
            ->get();

        return Inertia::render('GrowStream/Search', [
            'query' => $term,
            'videos' => $videos,
            'creators' => $creators,
            'series' => $series,
            'categories' => $categories,
            'trending' => $trending,
        ]);
    }

    public function channel(string $slug, Request $request): Response
    {
        $creator = CreatorProfile::with('user')
            ->where('is_active', true)
            ->where(function ($q) use ($slug) {
                $q->where('channel_slug', $slug)
                  ->orWhere('id', (int) $slug);
            })
            ->first();

        if (!$creator) {
            abort(404);
        }

        if ($this->attribution !== null) {
            $source = $request->query('src');
            $session = $this->attribution->newSessionId();
            $this->attribution->resolve((int) $creator->id, is_string($source) ? $source : null, $session);
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

    public function downloads(): Response
    {
        $downloadable = $this->videoRepo->query()
            ->published()
            ->where('is_downloadable', true)
            ->with(['creator.user', 'categories'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return Inertia::render('GrowStream/Downloads', [
            'downloadable' => $downloadable,
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
            'videos' => $this->paginateShape($videos),
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
        $period = (int) $request->get('period', 30);
        $startDate = now()->subDays($period);

        $totalVideos = $this->videoRepo->query()->count();
        $publishedVideos = $this->videoRepo->query()->published()->count();
        $newVideosThisPeriod = $this->videoRepo->query()->where('created_at', '>=', $startDate)->count();

        $totalViews = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView::count();
        $viewsThisPeriod = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView::where('created_at', '>=', $startDate)->count();

        $totalWatchTime = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory::sum('watch_duration') / 3600;
        $watchTimeThisPeriod = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory::where('created_at', '>=', $startDate)->sum('watch_duration') / 3600;

        $uniqueViewers = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView::distinct('user_id')->count('user_id');
        $uniqueViewersThisPeriod = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView::where('created_at', '>=', $startDate)->distinct('user_id')->count('user_id');

        $completedViews = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory::where('is_completed', true)->count();
        $totalWatchSessions = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory::count();
        $completionRate = $totalWatchSessions > 0 ? round(($completedViews / $totalWatchSessions) * 100, 2) : 0;
        $avgWatchDuration = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory::avg('watch_duration');

        $topCategories = \Illuminate\Support\Facades\DB::table('growstream_video_categories as c')
            ->join('growstream_video_category as vc', 'c.id', '=', 'vc.category_id')
            ->join('growstream_videos as v', 'vc.video_id', '=', 'v.id')
            ->join('growstream_video_views as vv', 'v.id', '=', 'vv.video_id')
            ->select('c.name', \Illuminate\Support\Facades\DB::raw('COUNT(vv.id) as view_count'))
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('view_count')
            ->limit(5)
            ->get();

        $dailyViews = \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView::where('created_at', '>=', $startDate)
            ->select(
                \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as views'),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT user_id) as unique_viewers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('GrowStream/Admin/Analytics', [
            'analytics' => [
                'overview' => [
                    'total_videos' => $totalVideos,
                    'published_videos' => $publishedVideos,
                    'new_videos_this_period' => $newVideosThisPeriod,
                    'total_views' => $totalViews,
                    'views_this_period' => $viewsThisPeriod,
                    'total_watch_time_hours' => round($totalWatchTime, 2),
                    'watch_time_this_period_hours' => round($watchTimeThisPeriod, 2),
                    'unique_viewers' => $uniqueViewers,
                    'unique_viewers_this_period' => $uniqueViewersThisPeriod,
                    'completion_rate' => $completionRate,
                    'avg_watch_duration_seconds' => round($avgWatchDuration, 2),
                ],
                'top_categories' => $topCategories,
                'daily_views' => $dailyViews,
            ],
        ]);
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
            'creators' => $this->paginateShape($creators),
        ]);
    }

    /**
     * Convert a LengthAwarePaginator into the { data, meta } shape the
     * frontend types (PaginatedResponse) expect. Laravel serializes a raw
     * paginator flat (current_page, last_page at top level), but the Vue
     * components read nested videos.meta / videos.data.
     */
    private function paginateShape(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
