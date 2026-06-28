<?php

namespace App\Domain\GrowStream\Infrastructure\Listeners;

use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyVideoProcessingCompleted implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(VideoProcessingCompleted $event): void
    {
        Log::info("NotifyVideoProcessingCompleted: Video processing completed", [
            'video_id' => $event->video->id,
            'title' => $event->video->title,
        ]);

        // TODO: Send notification to creator
        // Example: Notification::send($event->video->creator, new VideoReadyNotification($event->video));

        // Update video status if needed
        if (!$event->video->is_published && $event->video->upload_status === 'ready') {
            Log::info("NotifyVideoProcessingCompleted: Video ready for publishing", [
                'video_id' => $event->video->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(VideoProcessingCompleted $event, \Throwable $exception): void
    {
        Log::error("NotifyVideoProcessingCompleted: Failed to notify", [
            'video_id' => $event->video->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
