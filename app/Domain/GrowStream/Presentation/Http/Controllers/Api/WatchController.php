<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Api;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Watchlist;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchController extends Controller
{
    /**
     * Authorize playback and get signed URL
     */
    public function authorize(Request $request): JsonResponse
    {
        $request->validate([
            'video_id' => 'required|exists:growstream_videos,id',
        ]);

        $video = Video::findOrFail($request->video_id);
        $user = Auth::user();

        // Check if video is published and ready
        if (!$video->isPublished() || !$video->isReady()) {
            return response()->json([
                'success' => false,
                'error' => 'Video not available',
            ], 404);
        }

        // Check access level
        if (!$this->canWatch($user, $video)) {
            return response()->json([
                'success' => false,
                'error' => 'Subscription required',
                'required_access_level' => $video->access_level,
            ], 403);
        }

        // Get signed playback URL
        $provider = VideoProviderFactory::make($video->video_provider);
        $playbackUrl = $provider->getPlaybackUrl(
            $video->provider_video_id,
            signed: true,
            expiresIn: config('growstream.access.signed_url_expiration', 86400)
        );

        // Record view
        $this->recordView($video, $user, $request);

        // Get or create watch history
        $watchHistory = WatchHistory::firstOrCreate(
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

    /**
     * Update watch progress
     */
    public function updateProgress(Request $request): JsonResponse
    {
        $request->validate([
            'video_id' => 'required|exists:growstream_videos,id',
            'current_position' => 'required|integer|min:0',
            'duration' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($request->video_id);

        $watchHistory = WatchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'video_id' => $video->id,
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

    /**
     * Get watch history
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();

        $history = WatchHistory::with('video')
            ->where('user_id', $user->id)
            ->recent($request->get('days', 30))
            ->orderBy('last_watched_at', 'desc')
            ->paginate($request->get('per_page', 20));

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

    /**
     * Get continue watching list
     */
    public function continueWatching(): JsonResponse
    {
        $user = Auth::user();

        $continueWatching = WatchHistory::with('video.creator')
            ->where('user_id', $user->id)
            ->inProgress()
            ->orderBy('last_watched_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $continueWatching,
        ]);
    }

    /**
     * Get user's watchlist
     */
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

    /**
     * Add to watchlist
     */
    public function addToWatchlist(Request $request): JsonResponse
    {
        $request->validate([
            'watchlistable_type' => 'required|in:video,series',
            'watchlistable_id' => 'required|integer',
        ]);

        $user = Auth::user();
        
        $watchlistableType = $request->watchlistable_type === 'video' 
            ? Video::class 
            : VideoSeries::class;

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

    /**
     * Remove from watchlist
     */
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

    /**
     * Check if user can watch video
     */
    protected function canWatch($user, Video $video): bool
    {
        // Free content is accessible to everyone
        if ($video->isFree()) {
            return true;
        }

        // Require authentication for non-free content
        if (!$user) {
            return false;
        }

        // TODO: Implement subscription checking
        // For now, allow all authenticated users
        return true;
    }

    /**
     * Record video view
     */
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

        // Increment view count
        $video->increment('view_count');
    }

    /**
     * Get device type from request
     */
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
