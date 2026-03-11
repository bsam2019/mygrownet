<?php

namespace App\Domain\GrowStream\Infrastructure\Listeners;

use App\Domain\GrowStream\Infrastructure\Events\VideoUploaded;
use App\Domain\GrowStream\Infrastructure\Jobs\ProcessVideoJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleVideoUpload implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(VideoUploaded $event): void
    {
        Log::info("HandleVideoUpload: Video uploaded", [
            'video_id' => $event->video->id,
            'title' => $event->video->title,
        ]);

        // Dispatch processing job
        ProcessVideoJob::dispatch($event->video->id)
            ->delay(now()->addSeconds(10)); // Small delay to ensure upload is complete
    }

    /**
     * Handle a job failure.
     */
    public function failed(VideoUploaded $event, \Throwable $exception): void
    {
        Log::error("HandleVideoUpload: Failed to handle video upload", [
            'video_id' => $event->video->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
