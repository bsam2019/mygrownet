<?php

namespace App\Events\BizBoost;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $businessId,
        public array $customer
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bizboost.{$this->businessId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'customer.added';
    }

    public function broadcastWith(): array
    {
        return [
            'customer' => $this->customer,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
