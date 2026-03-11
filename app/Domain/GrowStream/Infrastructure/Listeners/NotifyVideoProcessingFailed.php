<?php

namespace App\Domain\GrowStream\Infrastructure\Listeners;

use App\Domain\GrowStream\Infrastructure\Events\VideoProcessingFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyVideoProcessingFailed implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(VideoProcessingFailed $event): void
    {
        Log::error("NotifyVideoProcessingFailed: Video processing failed", [
            'video_id' => $event->video->id,
            'title' => $event->video->title,
            'error' => $event->error,
        ]);

        // TODO: Send notification to creator and admin
        // Example: Notification::send($event->video->creator, new VideoFailedNotification($event->video, $event->error));
        // Example: Notification::send($admins, new VideoProcessingFailedAdminNotification($event->video, $event->error));

        // Log for monitoring/alerting
        Log::channel('slack')->error('Video processing failed', [
            'video_id' => $event->video->id,
            'title' => $event->video->title,
            'creator' => $event->video->creator->name ?? 'Unknown',
            'error' => $event->error,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(VideoProcessingFailed $event, \Throwable $exception): void
    {
        Log::error("NotifyVideoProcessingFailed: Failed to notify", [
            'video_id' => $event->video->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
