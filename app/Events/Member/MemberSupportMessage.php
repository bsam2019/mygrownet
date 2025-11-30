<?php

namespace App\Events\Member;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MemberSupportMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ticketId,
        public int $senderId,
        public string $senderName,
        public string $senderType, // 'member' or 'support'
        public string $message,
        public string $sentAt
    ) {
        Log::info('MemberSupportMessage event constructed', [
            'ticket_id' => $ticketId,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_type' => $senderType,
            'channel' => 'member.support.' . $ticketId,
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channel = 'member.support.' . $this->ticketId;
        Log::info('MemberSupportMessage broadcasting on channel', ['channel' => $channel]);
        
        return [
            new PrivateChannel($channel),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'sender_type' => $this->senderType,
            'message' => $this->message,
            'sent_at' => $this->sentAt,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'chat.message';
    }
}
