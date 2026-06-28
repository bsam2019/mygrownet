<?php

namespace App\Events\Employee;

use App\Models\EmployeeTask;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public EmployeeTask $task,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('employee.' . $this->task->assigned_to),
        ];

        // Also notify the assigner if different
        if ($this->task->assigned_by && $this->task->assigned_by !== $this->task->assigned_to) {
            $channels[] = new PrivateChannel('employee.' . $this->task->assigned_by);
        }

        // Broadcast to department channel
        if ($this->task->department_id) {
            $channels[] = new PrivateChannel('department.' . $this->task->department_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'assigned_to' => $this->task->assigned_to,
            'updated_at' => $this->task->updated_at->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'task.status.updated';
    }
}
