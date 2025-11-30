<?php

namespace App\Events\Employee;

use App\Models\EmployeeSupportTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmployeeSupportTicket $ticket
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.admin'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'category' => $this->ticket->category,
            'priority' => $this->ticket->priority,
            'status' => $this->ticket->status,
            'employee_id' => $this->ticket->employee_id,
            'employee_name' => $this->ticket->employee?->full_name,
            'department_name' => $this->ticket->employee?->department?->name,
            'created_at' => $this->ticket->created_at->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.created';
    }
}
