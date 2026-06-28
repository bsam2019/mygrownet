<?php

namespace App\Events\BizBoost;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $businessId,
        public array $post
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bizboost.{$this->businessId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'post.published';
    }

    public function broadcastWith(): array
    {
        return [
            'post' => $this->post,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
