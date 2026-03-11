<?php

namespace App\Domain\GrowStream\Infrastructure\Jobs;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateThumbnailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $videoId
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $video = Video::find($this->videoId);

        if (!$video) {
            Log::error("GenerateThumbnailsJob: Video not found", ['video_id' => $this->videoId]);
            return;
        }

        try {
            Log::info("GenerateThumbnailsJob: Starting thumbnail generation", ['video_id' => $this->videoId]);

            // Check if video provider already generated thumbnails
            if ($video->thumbnail_url) {
                Log::info("GenerateThumbnailsJob: Provider already generated thumbnails", [
                    'video_id' => $this->videoId,
                    'thumbnail_url' => $video->thumbnail_url,
                ]);
                return;
            }

            // TODO: Implement FFmpeg thumbnail generation
            // This is a placeholder for future implementation
            // For now, we'll use a default thumbnail or provider-generated one

            // Example FFmpeg command (to be implemented):
            // ffmpeg -i input.mp4 -ss 00:00:05 -vframes 1 thumbnail.jpg
            // ffmpeg -i input.mp4 -vf "fps=1/10" thumbnails/thumb_%04d.jpg

            $this->generatePlaceholderThumbnail($video);

            Log::info("GenerateThumbnailsJob: Thumbnail generation completed", ['video_id' => $this->videoId]);

        } catch (\Exception $e) {
            Log::error("GenerateThumbnailsJob: Exception occurred", [
                'video_id' => $this->videoId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate placeholder thumbnail (temporary solution)
     */
    protected function generatePlaceholderThumbnail(Video $video): void
    {
        // For MVP, we'll use a placeholder or the provider's thumbnail
        // In production, this would use FFmpeg to extract frames

        if (!$video->thumbnail_url) {
            // Generate a placeholder URL based on content type
            $placeholderMap = [
                'movie' => 'https://via.placeholder.com/1280x720/2563eb/ffffff?text=Movie',
                'series' => 'https://via.placeholder.com/1280x720/7c3aed/ffffff?text=Series',
                'episode' => 'https://via.placeholder.com/1280x720/7c3aed/ffffff?text=Episode',
                'lesson' => 'https://via.placeholder.com/1280x720/059669/ffffff?text=Lesson',
                'short' => 'https://via.placeholder.com/720x1280/d97706/ffffff?text=Short',
                'workshop' => 'https://via.placeholder.com/1280x720/4f46e5/ffffff?text=Workshop',
                'webinar' => 'https://via.placeholder.com/1280x720/4f46e5/ffffff?text=Webinar',
            ];

            $thumbnailUrl = $placeholderMap[$video->content_type] ?? $placeholderMap['movie'];

            $video->update([
                'thumbnail_url' => $thumbnailUrl,
                'poster_url' => $thumbnailUrl,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateThumbnailsJob: Job failed", [
            'video_id' => $this->videoId,
            'error' => $exception->getMessage(),
        ]);
    }
}
