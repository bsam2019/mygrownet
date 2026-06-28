<?php

namespace App\Domain\GrowStream\Infrastructure\Events;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoProcessingCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Video $video
    ) {}
}
