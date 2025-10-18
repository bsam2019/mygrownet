<?php

namespace App\Events;

use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PointsAwarded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public PointTransaction $transaction;

    public function __construct(User $user, PointTransaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'points.awarded';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'lp_amount' => $this->transaction->lp_amount,
            'map_amount' => $this->transaction->map_amount,
            'source' => $this->transaction->source,
            'description' => $this->transaction->description,
            'total_lp' => $this->user->points->lifetime_points,
            'total_map' => $this->user->points->monthly_points,
        ];
    }
}
