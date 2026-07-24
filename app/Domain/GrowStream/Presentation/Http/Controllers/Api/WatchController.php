<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private WatchHistoryRepositoryInterface $watchHistoryRepo
    ) {}

    public function authorize(Request $request): JsonResponse
    {
        $request->validate([
            'video_id' => 'required|exists:growstream_videos,id',
        ]);

        $video = $this->videoRepo->findById($request->video_id);
        if (!$video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        $user = Auth::user();

        if (!$video->isPublished() || !$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video not available',
            ], 404);
        }

        if (!$this->canWatch($user, $video)) {
            return response()->json([
                'success' => false,
                'error' => 'Subscription required',
                'required_access_level' => $video->access_level,
            ], 403);
        }

        $provider = VideoProviderFactory::make($video->video_provider);
        $playbackUrl = $provider->getPlaybackUrl(
            $video->provider_video_id,
            signed: true,
            expiresIn: config('growstream.access.signed_url_expiration', 86400)
        );

        $this->recordView($video, $user, $request);

        $watchHistory = $this->watchHistoryRepo->updateOrCreate(
            [
                'user_id' => $user->id,
                'video_id' => $video->id,
            ],
            [
                'duration' => $video->duration,
                'started_at' => now(),
                'last_watched_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'playback_url' => $playbackUrl,
                'expires_at' => now()->addSeconds(config('growstream.access.signed_url_expiration', 86400)),
                'current_position' => $watchHistory->current_position,
                'duration' => $video->duration,
            ],
        ]);
    }

    public function updateProgress(Request $request): JsonResponse
    {
        $request->validate([
            'video_id' => 'required|exists:growstream_videos,id',
            'current_position' => 'required|integer|min:0',
            'duration' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        $watchHistory = $this->watchHistoryRepo->updateOrCreate(
            [
                'user_id' => $user->id,
                'video_id' => $request->video_id,
            ],
            [
                'duration' => $request->duration,
                'started_at' => now(),
                'last_watched_at' => now(),
                'session_id' => $request->session_id,
                'device_type' => $request->device_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $watchHistory->updateProgress($request->current_position, $request->duration);

        return response()->json([
            'success' => true,
            'data' => [
                'progress_percentage' => $watchHistory->progress_percentage,
                'is_completed' => $watchHistory->is_completed,
            ],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();

        $history = $this->watchHistoryRepo->paginateForUser(
            $user->id,
            $request->get('per_page', 20),
            ['video']
        );

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'meta' => [
                'current_page' => $history->currentPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ]);
    }

    public function continueWatching(): JsonResponse
    {
        $user = Auth::user();

        $continueWatching = $this->watchHistoryRepo->continueWatching(
            $user->id,
            10,
            ['video.creator']
        );

        return response()->json([
            'success' => true,
            'data' => $continueWatching,
        ]);
    }

    public function watchlist(): JsonResponse
    {
        $user = Auth::user();

        $watchlist = Watchlist::with('watchlistable')
            ->where('user_id', $user->id)
            ->orderBy('added_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watchlist,
        ]);
    }

    public function addToWatchlist(Request $request): JsonResponse
    {
        $request->validate([
            'watchlistable_type' => 'required|in:video,series',
            'watchlistable_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $watchlistableType = $request->watchlistable_type === 'video'
            ? Video::class
            : \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries::class;

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => $user->id,
            'watchlistable_type' => $watchlistableType,
            'watchlistable_id' => $request->watchlistable_id,
        ]);

        return response()->json([
            'success' => true,
            'data' => $watchlist,
            'message' => 'Added to watchlist',
        ]);
    }

    public function removeFromWatchlist(Watchlist $watchlist): JsonResponse
    {
        $user = Auth::user();

        if ($watchlist->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], 403);
        }

        $watchlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from watchlist',
        ]);
    }

    protected function canWatch($user, Video $video): bool
    {
        if ($video->isFree()) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return true;
    }

    protected function recordView(Video $video, $user, Request $request): void
    {
        VideoView::create([
            'video_id' => $video->id,
            'user_id' => $user?->id,
            'watch_duration' => 0,
            'session_id' => $request->session_id ?? session()->getId(),
            'device_type' => $this->getDeviceType($request),
            'browser' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'referrer_url' => $request->header('referer'),
            'viewed_at' => now(),
        ]);

        $video->increment('view_count');
    }

    protected function getDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
