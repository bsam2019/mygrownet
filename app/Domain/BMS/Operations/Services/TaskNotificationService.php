<?php

namespace App\Domain\BMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\TaskModel;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class TaskNotificationService
{
    public function notifyTaskAssigned(TaskModel $task): void
    {
        if (!$task->assignedUser) {
            return;
        }

        // Send email notification
        $this->sendEmail($task->assignedUser, [
            'subject' => "New Task Assigned: {$task->title}",
            'message' => "You have been assigned a new task: {$task->title}",
            'action_url' => route('bms.operations.tasks.show', $task->id),
            'action_text' => 'View Task',
        ]);

        // Create in-app notification
        $this->createNotification($task->assignedUser, [
            'title' => 'New Task Assigned',
            'message' => "You have been assigned: {$task->title}",
            'type' => 'task_assigned',
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'url' => route('bms.operations.tasks.show', $task->id),
            ],
        ]);
    }

    public function notifyTaskReassigned(TaskModel $task, User $oldAssignee): void
    {
        // Notify old assignee
        if ($oldAssignee) {
            $this->createNotification($oldAssignee, [
                'title' => 'Task Reassigned',
                'message' => "Task '{$task->title}' has been reassigned to someone else",
                'type' => 'task_reassigned',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                ],
            ]);
        }

        // Notify new assignee
        if ($task->assignedUser) {
            $this->notifyTaskAssigned($task);
        }
    }

    public function notifyTaskDueSoon(TaskModel $task): void
    {
        if (!$task->assignedUser || !$task->due_date) {
            return;
        }

        $daysUntilDue = now()->diffInDays($task->due_date, false);

        if ($daysUntilDue <= 1 && $daysUntilDue >= 0) {
            $this->createNotification($task->assignedUser, [
                'title' => 'Task Due Soon',
                'message' => "Task '{$task->title}' is due " . ($daysUntilDue == 0 ? 'today' : 'tomorrow'),
                'type' => 'task_due_soon',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_date' => $task->due_date->toDateString(),
                    'url' => route('bms.operations.tasks.show', $task->id),
                ],
            ]);
        }
    }

    public function notifyTaskOverdue(TaskModel $task): void
    {
        if (!$task->assignedUser) {
            return;
        }

        $this->createNotification($task->assignedUser, [
            'title' => 'Task Overdue',
            'message' => "Task '{$task->title}' is overdue",
            'type' => 'task_overdue',
            'data' => [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'due_date' => $task->due_date->toDateString(),
                'url' => route('bms.operations.tasks.show', $task->id),
            ],
        ]);
    }

    public function notifyTaskCompleted(TaskModel $task): void
    {
        // Notify creator
        if ($task->creator && $task->creator->id !== $task->assigned_to) {
            $this->createNotification($task->creator, [
                'title' => 'Task Completed',
                'message' => "{$task->assignedUser->name} completed task: {$task->title}",
                'type' => 'task_completed',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'completed_by' => $task->assignedUser->name,
                    'url' => route('bms.operations.tasks.show', $task->id),
                ],
            ]);
        }

        // Notify watchers
        foreach ($task->watchers as $watcher) {
            if ($watcher->user_id !== $task->assigned_to && $watcher->user_id !== $task->created_by) {
                $this->createNotification($watcher->user, [
                    'title' => 'Task Completed',
                    'message' => "Task '{$task->title}' has been completed",
                    'type' => 'task_completed',
                    'data' => [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'url' => route('bms.operations.tasks.show', $task->id),
                    ],
                ]);
            }
        }
    }

    public function notifyTaskBlocked(TaskModel $task, string $reason): void
    {
        // Notify creator
        if ($task->creator && $task->creator->id !== $task->assigned_to) {
            $this->createNotification($task->creator, [
                'title' => 'Task Blocked',
                'message' => "Task '{$task->title}' has been blocked: {$reason}",
                'type' => 'task_blocked',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'reason' => $reason,
                    'url' => route('bms.operations.tasks.show', $task->id),
                ],
            ]);
        }
    }

    public function notifyTaskCommented(TaskModel $task, User $commenter, string $comment): void
    {
        $recipients = collect();

        // Add creator
        if ($task->creator && $task->creator->id !== $commenter->id) {
            $recipients->push($task->creator);
        }

        // Add assignee
        if ($task->assignedUser && $task->assignedUser->id !== $commenter->id) {
            $recipients->push($task->assignedUser);
        }

        // Add watchers
        foreach ($task->watchers as $watcher) {
            if ($watcher->user_id !== $commenter->id) {
                $recipients->push($watcher->user);
            }
        }

        // Remove duplicates
        $recipients = $recipients->unique('id');

        foreach ($recipients as $recipient) {
            $this->createNotification($recipient, [
                'title' => 'New Comment',
                'message' => "{$commenter->name} commented on '{$task->title}'",
                'type' => 'task_commented',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'commenter' => $commenter->name,
                    'comment' => substr($comment, 0, 100),
                    'url' => route('bms.operations.tasks.show', $task->id),
                ],
            ]);
        }
    }

    private function sendEmail(User $user, array $data): void
    {
        // TODO: Implement email sending
        // Mail::to($user->email)->send(new TaskNotificationMail($data));
    }

    private function createNotification(User $user, array $data): void
    {
        // Create notification in database using custom structure with polymorphic relationship
        \DB::table('notifications')->insert([
            'id' => \Str::uuid(),
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'type' => $data['type'] ?? 'task',
            'category' => 'operations',
            'title' => $data['title'],
            'message' => $data['message'],
            'action_url' => $data['data']['url'] ?? null,
            'action_text' => 'View Task',
            'data' => json_encode($data['data'] ?? []),
            'priority' => 'normal',
            'created_at' => now(),
        ]);
    }
}
