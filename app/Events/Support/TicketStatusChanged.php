<?php

namespace App\Events\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ticketId,
        public string $status,
        public string $source,
        public ?string $closedBy = null
    ) {}

    public function broadcastOn(): array
    {
        // Broadcast to the appropriate channel based on source
        $channelName = match ($this->source) {
            'investor' => "investor.support.{$this->ticketId}",
            'member' => "member.support.{$this->ticketId}",
            default => "support.ticket.{$this->ticketId}",
        };

        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.status';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'status' => $this->status,
            'closed_by' => $this->closedBy,
        ];
    }
}
