<?php

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrowStreamStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'growstream:stats';

    /**
     * The console command description.
     */
    protected $description = 'Display GrowStream platform statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("GrowStream Platform Statistics");
        $this->newLine();

        // Content Stats
        $this->line("📹 <fg=cyan>Content Statistics</>");
        $totalVideos = Video::count();
        $publishedVideos = Video::published()->count();
        $totalSeries = VideoSeries::count();
        $publishedSeries = VideoSeries::published()->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Videos', $totalVideos],
                ['Published Videos', $publishedVideos],
                ['Total Series', $totalSeries],
                ['Published Series', $publishedSeries],
            ]
        );

        // Upload Status
        $this->newLine();
        $this->line("⚙️  <fg=cyan>Upload Status</>");
        $statusCounts = Video::select('upload_status', DB::raw('count(*) as count'))
            ->groupBy('upload_status')
            ->get()
            ->map(fn($item) => [$item->upload_status, $item->count])
            ->toArray();

        $this->table(['Status', 'Count'], $statusCounts);

        // View Stats
        $this->newLine();
        $this->line("👁️  <fg=cyan>Viewing Statistics</>");
        $totalViews = VideoView::count();
        $uniqueViewers = VideoView::distinct('user_id')->count('user_id');
        $totalWatchTime = WatchHistory::sum('current_position');
        $avgWatchTime = WatchHistory::avg('current_position');
        $completionRate = $this->calculateCompletionRate();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Views', number_format($totalViews)],
                ['Unique Viewers', number_format($uniqueViewers)],
                ['Total Watch Time', $this->formatDuration($totalWatchTime)],
                ['Avg Watch Time', $this->formatDuration($avgWatchTime)],
                ['Completion Rate', round($completionRate, 2) . '%'],
            ]
        );

        // Creator Stats
        $this->newLine();
        $this->line("👤 <fg=cyan>Creator Statistics</>");
        $totalCreators = CreatorProfile::count();
        $verifiedCreators = CreatorProfile::where('is_verified', true)->count();
        $activeCreators = CreatorProfile::where('is_active', true)->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Creators', $totalCreators],
                ['Verified Creators', $verifiedCreators],
                ['Active Creators', $activeCreators],
            ]
        );

        // Recent Activity
        $this->newLine();
        $this->line("📊 <fg=cyan>Recent Activity (Last 7 Days)</>");
        $recentViews = VideoView::where('viewed_at', '>=', now()->subDays(7))->count();
        $recentVideos = Video::where('created_at', '>=', now()->subDays(7))->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['New Views', number_format($recentViews)],
                ['New Videos', $recentVideos],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Calculate overall completion rate
     */
    protected function calculateCompletionRate(): float
    {
        $total = WatchHistory::count();
        if ($total === 0) {
            return 0;
        }

        $completed = WatchHistory::where('is_completed', true)->count();
        return ($completed / $total) * 100;
    }

    /**
     * Format duration in seconds to human readable format
     */
    protected function formatDuration(?float $seconds): string
    {
        if (!$seconds) {
            return '0s';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if ($secs > 0 || empty($parts)) {
            $parts[] = "{$secs}s";
        }

        return implode(' ', $parts);
    }
}
