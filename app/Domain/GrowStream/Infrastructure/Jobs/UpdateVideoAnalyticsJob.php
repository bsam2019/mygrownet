<?php

namespace App\Domain\GrowStream\Infrastructure\Jobs;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateVideoAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $videoId,
        public ?string $date = null
    ) {
        $this->onQueue('low');
        $this->date = $date ?? now()->toDateString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $video = Video::find($this->videoId);

        if (!$video) {
            Log::error("UpdateVideoAnalyticsJob: Video not found", ['video_id' => $this->videoId]);
            return;
        }

        try {
            Log::info("UpdateVideoAnalyticsJob: Updating analytics", [
                'video_id' => $this->videoId,
                'date' => $this->date,
            ]);

            // Calculate daily analytics
            $analytics = DB::table('growstream_video_views')
                ->where('video_id', $this->videoId)
                ->whereDate('created_at', $this->date)
                ->select([
                    DB::raw('COUNT(*) as total_views'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_viewers'),
                    DB::raw('AVG(watch_duration) as avg_watch_duration'),
                ])
                ->first();

            // Get watch history stats
            $watchStats = DB::table('growstream_watch_history')
                ->where('video_id', $this->videoId)
                ->whereDate('created_at', $this->date)
                ->select([
                    DB::raw('SUM(watch_duration) as total_watch_time'),
                    DB::raw('COUNT(CASE WHEN is_completed = 1 THEN 1 END) as completions'),
                    DB::raw('COUNT(*) as total_sessions'),
                ])
                ->first();

            // Calculate completion rate
            $completionRate = $watchStats->total_sessions > 0
                ? ($watchStats->completions / $watchStats->total_sessions) * 100
                : 0;

            // Upsert daily analytics
            DB::table('growstream_video_analytics_daily')->updateOrInsert(
                [
                    'video_id' => $this->videoId,
                    'date' => $this->date,
                ],
                [
                    'views' => $analytics->total_views ?? 0,
                    'unique_viewers' => $analytics->unique_viewers ?? 0,
                    'watch_time_seconds' => $watchStats->total_watch_time ?? 0,
                    'avg_watch_duration' => $analytics->avg_watch_duration ?? 0,
                    'completion_rate' => round($completionRate, 2),
                    'completions' => $watchStats->completions ?? 0,
                    'updated_at' => now(),
                ]
            );

            // Update video aggregate stats
            $this->updateVideoAggregateStats($video);

            Log::info("UpdateVideoAnalyticsJob: Analytics updated successfully", [
                'video_id' => $this->videoId,
                'views' => $analytics->total_views ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error("UpdateVideoAnalyticsJob: Exception occurred", [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update video aggregate statistics
     */
    protected function updateVideoAggregateStats(Video $video): void
    {
        $totalViews = $video->views()->count();
        $totalWatchTime = $video->watchHistory()->sum('watch_duration');
        $avgWatchDuration = $video->watchHistory()->avg('watch_duration');
        
        $completedWatches = $video->watchHistory()->where('is_completed', true)->count();
        $totalWatches = $video->watchHistory()->count();
        $completionRate = $totalWatches > 0 ? ($completedWatches / $totalWatches) * 100 : 0;

        $video->update([
            'view_count' => $totalViews,
            'total_watch_time' => $totalWatchTime,
            'avg_watch_duration' => $avgWatchDuration,
            'completion_rate' => round($completionRate, 2),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UpdateVideoAnalyticsJob: Job failed", [
            'video_id' => $this->videoId,
            'date' => $this->date,
            'error' => $exception->getMessage(),
        ]);
    }
}
