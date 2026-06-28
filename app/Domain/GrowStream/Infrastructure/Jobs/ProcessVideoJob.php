<?php

namespace App\Domain\GrowStream\Infrastructure\Jobs;

use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingCompleted;
use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingFailed;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Providers\VideoProviderFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // 10 minutes
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $videoId
    ) {
        $this->onQueue('high'); // High priority queue for video processing
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $video = Video::find($this->videoId);

        if (!$video) {
            Log::error("ProcessVideoJob: Video not found", ['video_id' => $this->videoId]);
            return;
        }

        try {
            Log::info("ProcessVideoJob: Starting processing", ['video_id' => $this->videoId]);

            // Update status to processing
            $video->update(['upload_status' => 'processing']);

            // Get video provider
            $provider = VideoProviderFactory::make($video->video_provider);

            // Check processing status from provider
            $status = $provider->getStatus($video->provider_video_id);

            // Update video with latest information
            $video->update([
                'upload_status' => $status->status,
                'playback_url' => $status->playbackUrl ?? $video->playback_url,
                'thumbnail_url' => $status->thumbnailUrl ?? $video->thumbnail_url,
                'duration' => $status->duration ?? $video->duration,
                'processing_completed_at' => $status->status === 'ready' ? now() : null,
            ]);

            if ($status->status === 'ready') {
                Log::info("ProcessVideoJob: Processing completed", ['video_id' => $this->videoId]);

                // Dispatch completion event
                event(new VideoProcessingCompleted($video));

                // Chain additional jobs
                GenerateThumbnailsJob::dispatch($this->videoId)
                    ->onQueue('default');

                UpdateVideoAnalyticsJob::dispatch($this->videoId)
                    ->onQueue('low')
                    ->delay(now()->addMinutes(5));

            } elseif ($status->status === 'processing') {
                // Still processing, retry in 30 seconds
                Log::info("ProcessVideoJob: Still processing, will retry", ['video_id' => $this->videoId]);
                $this->release(30);

            } elseif ($status->status === 'failed') {
                Log::error("ProcessVideoJob: Processing failed", [
                    'video_id' => $this->videoId,
                    'error' => $status->error ?? 'Unknown error',
                ]);

                event(new VideoProcessingFailed($video, $status->error ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("ProcessVideoJob: Exception occurred", [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $video->update([
                'upload_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);

            event(new VideoProcessingFailed($video, $e->getMessage()));

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessVideoJob: Job failed permanently", [
            'video_id' => $this->videoId,
            'error' => $exception->getMessage(),
        ]);

        $video = Video::find($this->videoId);
        if ($video) {
            $video->update([
                'upload_status' => 'failed',
                'processing_error' => 'Processing failed after multiple attempts: ' . $exception->getMessage(),
            ]);

            event(new VideoProcessingFailed($video, $exception->getMessage()));
        }
    }
}
