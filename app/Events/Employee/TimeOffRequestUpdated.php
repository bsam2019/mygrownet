<?php

namespace App\Events\Employee;

use App\Models\EmployeeTimeOffRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeOffRequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmployeeTimeOffRequest $request,
        public string $action // 'approved', 'rejected', 'cancelled'
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('employee.' . $this->request->employee_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'request_id' => $this->request->id,
            'type' => $this->request->type,
            'start_date' => $this->request->start_date->toDateString(),
            'end_date' => $this->request->end_date->toDateString(),
            'status' => $this->request->status,
            'action' => $this->action,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'timeoff.updated';
    }
}
