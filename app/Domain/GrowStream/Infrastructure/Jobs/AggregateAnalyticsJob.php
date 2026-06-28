<?php

namespace App\Domain\GrowStream\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $date = null
    ) {
        $this->onQueue('low');
        $this->date = $date ?? now()->subDay()->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("AggregateAnalyticsJob: Starting aggregation", ['date' => $this->date]);

            // Get all videos that had activity on this date
            $videoIds = DB::table('growstream_video_views')
                ->whereDate('created_at', $this->date)
                ->distinct()
                ->pluck('video_id');

            Log::info("AggregateAnalyticsJob: Found videos with activity", [
                'date' => $this->date,
                'count' => $videoIds->count(),
            ]);

            // Dispatch individual video analytics jobs
            foreach ($videoIds as $videoId) {
                UpdateVideoAnalyticsJob::dispatch($videoId, $this->date)
                    ->onQueue('low');
            }

            // Aggregate platform-wide metrics
            $this->aggregatePlatformMetrics();

            Log::info("AggregateAnalyticsJob: Aggregation completed", ['date' => $this->date]);

        } catch (\Exception $e) {
            Log::error("AggregateAnalyticsJob: Exception occurred", [
                'date' => $this->date,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Aggregate platform-wide metrics
     */
    protected function aggregatePlatformMetrics(): void
    {
        // Total views for the day
        $viewStats = DB::table('growstream_video_views')
            ->whereDate('created_at', $this->date)
            ->select([
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT user_id) as unique_viewers'),
                DB::raw('COUNT(DISTINCT video_id) as videos_watched'),
            ])
            ->first();

        // Watch time stats
        $watchStats = DB::table('growstream_watch_history')
            ->whereDate('created_at', $this->date)
            ->select([
                DB::raw('SUM(watch_duration) as total_watch_time'),
                DB::raw('AVG(watch_duration) as avg_watch_duration'),
                DB::raw('COUNT(CASE WHEN is_completed = 1 THEN 1 END) as completions'),
                DB::raw('COUNT(*) as total_sessions'),
            ])
            ->first();

        // New videos published
        $newVideos = DB::table('growstream_videos')
            ->whereDate('published_at', $this->date)
            ->where('is_published', true)
            ->count();

        // Active creators
        $activeCreators = DB::table('growstream_videos as v')
            ->join('growstream_video_views as vv', 'v.id', '=', 'vv.video_id')
            ->whereDate('vv.created_at', $this->date)
            ->distinct()
            ->count('v.creator_id');

        // Calculate completion rate
        $completionRate = $watchStats->total_sessions > 0
            ? ($watchStats->completions / $watchStats->total_sessions) * 100
            : 0;

        // Store platform analytics (if table exists)
        // This is optional - can be added in Phase 2
        Log::info("AggregateAnalyticsJob: Platform metrics", [
            'date' => $this->date,
            'total_views' => $viewStats->total_views ?? 0,
            'unique_viewers' => $viewStats->unique_viewers ?? 0,
            'total_watch_time_hours' => round(($watchStats->total_watch_time ?? 0) / 3600, 2),
            'completion_rate' => round($completionRate, 2),
            'new_videos' => $newVideos,
            'active_creators' => $activeCreators,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AggregateAnalyticsJob: Job failed", [
            'date' => $this->date,
            'error' => $exception->getMessage(),
        ]);
    }
}
