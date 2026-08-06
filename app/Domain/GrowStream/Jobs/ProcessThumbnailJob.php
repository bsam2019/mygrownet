<?php

namespace App\Domain\GrowStream\Jobs;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Services\ThumbnailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param int $videoId
     * @param string $thumbnailPath Temporary storage path of uploaded file
     */
    public function __construct(
        public int $videoId,
        public string $thumbnailPath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ThumbnailService $thumbnailService): void
    {
        $video = Video::find($this->videoId);
        
        if (!$video) {
            Log::warning("ProcessThumbnailJob: Video {$this->videoId} not found");
            return;
        }

        try {
            // Get the uploaded file from temporary storage
            $tempDisk = Storage::disk('local');
            
            if (!$tempDisk->exists($this->thumbnailPath)) {
                Log::error("ProcessThumbnailJob: Temp file not found: {$this->thumbnailPath}");
                return;
            }

            $tempFilePath = $tempDisk->path($this->thumbnailPath);
            
            // Create UploadedFile instance from temp file
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFilePath,
                basename($this->thumbnailPath),
                mime_content_type($tempFilePath),
                null,
                true
            );

            // Delete old thumbnails if they exist
            if ($video->thumbnail_sizes && $video->thumbnail_storage_disk === 'wasabi') {
                Log::info("ProcessThumbnailJob: Deleting old thumbnails for video {$this->videoId}");
                $thumbnailService->deleteThumbnails(
                    $video->thumbnail_sizes,
                    config('growstream.thumbnails.disk')
                );
            }

            // Process and upload new thumbnails
            Log::info("ProcessThumbnailJob: Processing thumbnail for video {$this->videoId}");
            $thumbnailUrls = $thumbnailService->processAndStore($uploadedFile, (string) $this->videoId);

            // Update video record
            $video->update([
                'thumbnail_storage_disk' => 'wasabi',
                'thumbnail_sizes' => $thumbnailUrls,
                'thumbnail_url' => $thumbnailUrls['medium'], // Set medium as default
            ]);

            Log::info("ProcessThumbnailJob: Successfully processed thumbnail for video {$this->videoId}", [
                'sizes' => array_keys($thumbnailUrls),
            ]);

            // Clean up temporary file
            $tempDisk->delete($this->thumbnailPath);

        } catch (\Exception $e) {
            Log::error("ProcessThumbnailJob: Failed to process thumbnail for video {$this->videoId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up temp file on error
            if (isset($tempDisk) && $tempDisk->exists($this->thumbnailPath)) {
                $tempDisk->delete($this->thumbnailPath);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessThumbnailJob: Job failed after {$this->tries} attempts for video {$this->videoId}", [
            'error' => $exception->getMessage(),
        ]);

        // Clean up temp file if it still exists
        $tempDisk = Storage::disk('local');
        if ($tempDisk->exists($this->thumbnailPath)) {
            $tempDisk->delete($this->thumbnailPath);
        }
    }
}
