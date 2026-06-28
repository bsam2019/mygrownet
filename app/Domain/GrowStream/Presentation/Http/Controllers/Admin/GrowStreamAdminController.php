<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Models\User;
use App\Models\PointTransaction;
use App\Services\PointService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GrowStreamAdminController extends Controller
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * GrowStream Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_videos' => Video::count(),
            'published_videos' => Video::published()->count(),
            'total_series' => VideoSeries::count(),
            'total_views' => VideoView::count(),
            'unique_viewers' => VideoView::distinct('user_id')->count('user_id'),
            'completion_rate' => $this->getCompletionRate(),
            'avg_watch_time' => $this->getAverageWatchTime(),
            'points_awarded' => $this->getTotalPointsAwarded(),
        ];

        $recentVideos = Video::with(['creator.user', 'categories'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'creator' => $video->creator->user->name ?? 'Unknown',
                'status' => $video->upload_status,
                'is_published' => $video->is_published,
                'view_count' => $video->view_count,
                'created_at' => $video->created_at->format('Y-m-d H:i'),
            ]);

        $topVideos = Video::published()
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get()
            ->map(fn($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'view_count' => $video->view_count,
                'completion_rate' => $this->getVideoCompletionRate($video->id),
                'points_awarded' => $this->getVideoPointsAwarded($video->id),
            ]);

        return Inertia::render('Admin/GrowStream/Dashboard', [
            'stats' => $stats,
            'recentVideos' => $recentVideos,
            'topVideos' => $topVideos,
            'viewTrends' => $this->getViewTrends(),
            'pointsDistribution' => $this->getPointsDistribution(),
        ]);
    }

    /**
     * Manage point rewards for GrowStream content
     * Redirects to centralized bonus point settings
     */
    public function pointRewards()
    {
        // Redirect to centralized bonus point settings with GrowStream filter
        return redirect()->route('admin.settings.bp.index')
            ->with('info', 'GrowStream point rewards are managed through the centralized Bonus Point Settings.');
    }

    /**
     * Award points to users for GrowStream activities
     */
    public function awardPoints(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'activity_type' => 'required|string|in:video_watch,video_completion,series_completion,creator_bonus',
            'lp_amount' => 'required|integer|min:0|max:1000',
            'bp_amount' => 'required|integer|min:0|max:1000',
            'description' => 'required|string|max:255',
            'reference_id' => 'nullable|integer',
        ]);

        $awarded = 0;
        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if ($user) {
                $this->pointService->awardPoints(
                    user: $user,
                    source: 'growstream_' . $request->activity_type,
                    lpAmount: $request->lp_amount,
                    mapAmount: $request->bp_amount,
                    description: $request->description,
                    reference: $request->reference_id
                );
                $awarded++;
            }
        }

        return back()->with('success', "Points awarded to {$awarded} users successfully.");
    }

    /**
     * Manage GrowStream integration with starter kits
     */
    public function starterKitIntegration(Request $request)
    {
        // Get videos that can be added to starter kits
        $availableVideos = Video::published()
            ->with(['creator.user', 'categories'])
            ->where('is_starter_kit_content', false)
            ->get()
            ->map(fn($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'creator' => $video->creator->user->name ?? 'Unknown',
                'duration' => $video->getFormattedDuration(),
                'view_count' => $video->view_count,
                'categories' => $video->categories->pluck('name')->join(', '),
                'current_points' => $video->watch_points + $video->completion_points + $video->share_points,
            ]);

        // Get videos already in starter kits
        $starterKitVideos = Video::published()
            ->with(['creator.user'])
            ->where('is_starter_kit_content', true)
            ->orderBy('starter_kit_unlock_order')
            ->get()
            ->map(fn($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'creator' => $video->creator->user->name ?? 'Unknown',
                'starter_kit_tier' => $video->getStarterKitTierLabel(),
                'unlock_order' => $video->starter_kit_unlock_order,
                'points_reward' => $video->starter_kit_points_reward,
                'total_points' => $video->getTotalPointsReward(),
                'view_count' => $video->view_count,
            ]);

        return Inertia::render('Admin/GrowStream/StarterKitIntegration', [
            'availableVideos' => $availableVideos,
            'starterKitVideos' => $starterKitVideos,
        ]);
    }

    /**
     * Add video to starter kit
     */
    public function addToStarterKit(Request $request, Video $video)
    {
        $request->validate([
            'tier' => 'required|string|in:basic,premium,elite,all',
            'unlock_order' => 'required|integer|min:1|max:100',
            'points_reward' => 'required|integer|min:0|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        // Update video with starter kit information
        $video->update([
            'is_starter_kit_content' => true,
            'starter_kit_tier' => $request->tier,
            'starter_kit_unlock_order' => $request->unlock_order,
            'starter_kit_points_reward' => $request->points_reward,
        ]);

        // Create starter kit content entry
        \App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel::create([
            'title' => $video->title,
            'description' => $request->description ?? $video->description,
            'category' => 'video',
            'tier_restriction' => $request->tier === 'all' ? null : $request->tier,
            'unlock_day' => $request->unlock_order,
            'file_url' => route('growstream.video.show', $video->slug),
            'file_type' => 'growstream_video',
            'is_downloadable' => false,
            'estimated_value' => $request->points_reward,
            'sort_order' => $request->unlock_order,
            'is_active' => true,
        ]);

        return back()->with('success', 'Video added to starter kit successfully.');
    }

    /**
     * Remove video from starter kit
     */
    public function removeFromStarterKit(Video $video)
    {
        // Update video to remove starter kit information
        $video->update([
            'is_starter_kit_content' => false,
            'starter_kit_tier' => null,
            'starter_kit_unlock_order' => null,
            'starter_kit_points_reward' => 0,
        ]);

        // Remove from starter kit content items
        \App\Infrastructure\Persistence\Eloquent\StarterKit\ContentItemModel::where('file_type', 'growstream_video')
            ->where('file_url', route('growstream.video.show', $video->slug))
            ->delete();

        return back()->with('success', 'Video removed from starter kit successfully.');
    }

    // Private helper methods

    private function getCompletionRate(): float
    {
        $totalViews = VideoView::count();
        if ($totalViews === 0) return 0;

        $completedViews = WatchHistory::where('is_completed', true)->count();
        return round(($completedViews / $totalViews) * 100, 1);
    }

    private function getAverageWatchTime(): int
    {
        return (int) WatchHistory::avg('current_position') ?? 0;
    }

    private function getTotalPointsAwarded(): int
    {
        return PointTransaction::where('source', 'like', 'growstream_%')->sum('lp_amount');
    }

    private function getVideoCompletionRate(int $videoId): float
    {
        $totalViews = VideoView::where('video_id', $videoId)->count();
        if ($totalViews === 0) return 0;

        $completedViews = WatchHistory::where('video_id', $videoId)
            ->where('is_completed', true)
            ->count();

        return round(($completedViews / $totalViews) * 100, 1);
    }

    private function getVideoPointsAwarded(int $videoId): int
    {
        return PointTransaction::where('reference_type', Video::class)
            ->where('reference_id', $videoId)
            ->sum('lp_amount');
    }

    private function getViewTrends(): array
    {
        return VideoView::where('viewed_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getPointsDistribution(): array
    {
        return PointTransaction::where('source', 'like', 'growstream_%')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('source, SUM(lp_amount) as total_lp, SUM(bp_amount) as total_bp, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->toArray();
    }

    private function getDetailedViewTrends(): array
    {
        return VideoView::where('viewed_at', '>=', now()->subDays(90))
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as views, COUNT(DISTINCT user_id) as unique_viewers')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getUserEngagement(): array
    {
        return DB::table('users')
            ->join('growstream_video_views', 'users.id', '=', 'growstream_video_views.user_id')
            ->selectRaw('users.id, users.name, COUNT(*) as total_views, AVG(growstream_video_views.watch_duration) as avg_watch_time')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_views')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getContentPerformance(): array
    {
        return Video::published()
            ->selectRaw('id, title, view_count, (SELECT COUNT(*) FROM growstream_watch_history WHERE video_id = growstream_videos.id AND is_completed = true) as completions')
            ->orderByDesc('view_count')
            ->take(20)
            ->get()
            ->toArray();
    }

    private function getPointsAnalytics(): array
    {
        return [
            'total_awarded' => PointTransaction::where('source', 'like', 'growstream_%')->sum('lp_amount'),
            'by_activity' => PointTransaction::where('source', 'like', 'growstream_%')
                ->selectRaw('source, SUM(lp_amount) as total, COUNT(*) as count')
                ->groupBy('source')
                ->get()
                ->toArray(),
            'top_earners' => DB::table('users')
                ->join('point_transactions', 'users.id', '=', 'point_transactions.user_id')
                ->where('point_transactions.source', 'like', 'growstream_%')
                ->selectRaw('users.id, users.name, SUM(point_transactions.lp_amount) as total_points')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_points')
                ->take(10)
                ->get()
                ->toArray(),
        ];
    }

}