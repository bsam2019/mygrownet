<?php

namespace App\Events\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast when a new support ticket is created from any source
 * (investor, member, or employee)
 */
class UnifiedTicketCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ticketId,
        public string $source, // 'investor', 'member', or 'employee'
        public string $ticketNumber,
        public string $subject,
        public string $requesterName,
        public string $priority,
        public string $category,
        public string $createdAt
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.admin'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'source' => $this->source,
            'ticket_number' => $this->ticketNumber,
            'subject' => $this->subject,
            'requester_name' => $this->requesterName,
            'priority' => $this->priority,
            'category' => $this->category,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.created';
    }
}
