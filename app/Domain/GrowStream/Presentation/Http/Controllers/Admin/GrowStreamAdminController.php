<?php

namespace App\Domain\GrowStream\Presentation\Http\Controllers\Admin;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPlatform;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use App\Domain\GrowStream\Repositories\WatchHistoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Domain\GrowNet\Services\PointService;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\GrowNet\PointTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GrowStreamAdminController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private VideoSeriesRepositoryInterface $seriesRepo,
        private WatchHistoryRepositoryInterface $watchHistoryRepo,
        private PointService $pointService
    ) {}

    public function dashboard()
    {
        $stats = [
            'total_videos' => $this->videoRepo->query()->count(),
            'published_videos' => $this->videoRepo->query()->published()->count(),
            'pending_moderation_count' => $this->videoRepo->query()->where('upload_status', 'pending')->count(),
            'total_series' => $this->seriesRepo->query()->count(),
            'total_views' => VideoView::count(),
            'unique_viewers' => VideoView::distinct('user_id')->count('user_id'),
            'total_subscribers' => User::where('is_admin', false)->count(),
            'completion_rate' => $this->getCompletionRate(),
            'avg_watch_time' => $this->getAverageWatchTime(),
            'points_awarded' => $this->getTotalPointsAwarded(),
            'active_hubs_count' => CreatorPlatform::where('is_active', true)->count(),
            'total_hubs_count' => CreatorPlatform::count(),
        ];

        $recentVideos = $this->videoRepo->query()
            ->with(['creator.user', 'categories'])
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

        $topVideos = $this->videoRepo->query()
            ->published()
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

        $hubs = CreatorPlatform::latest()
            ->take(10)
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'brand_name' => $h->brand_name,
                'subdomain' => $h->subdomain,
                'subscription_plan' => $h->subscription_plan,
                'subscription_status' => $h->subscription_status,
                'is_active' => (bool) $h->is_active,
                'created_at' => $h->created_at ? $h->created_at->format('Y-m-d') : '',
            ]);

        return Inertia::render('Admin/GrowStream/Dashboard', [
            'stats' => $stats,
            'recentVideos' => $recentVideos,
            'topVideos' => $topVideos,
            'hubs' => $hubs,
            'viewTrends' => $this->getViewTrends(),
            'pointsDistribution' => $this->getPointsDistribution(),
        ]);
    }

    public function pointRewards()
    {
        return redirect()->route('admin.settings.bp.index')
            ->with('info', 'GrowStream point rewards are managed through the centralized Bonus Point Settings.');
    }

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
        return PointTransaction::where('reference_type', \App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video::class)
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
}
