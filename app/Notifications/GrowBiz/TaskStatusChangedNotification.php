<?php

namespace App\Notifications\GrowBiz;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $taskId,
        private string $taskTitle,
        private string $changerName,
        private string $oldStatus,
        private string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Status Updated: ' . $this->taskTitle)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A task you\'re involved with has been updated.')
            ->line('**Task:** ' . $this->taskTitle)
            ->line("**Status:** {$this->formatStatus($this->oldStatus)} → {$this->formatStatus($this->newStatus)}")
            ->line('**Updated by:** ' . $this->changerName)
            ->action('View Task', url('/growbiz/tasks/' . $this->taskId))
            ->line('Thank you for using GrowBiz!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'growbiz_task_status_changed',
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'changer_name' => $this->changerName,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "{$this->changerName} changed \"{$this->taskTitle}\" to {$this->formatStatus($this->newStatus)}",
            'url' => '/growbiz/tasks/' . $this->taskId,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function formatStatus(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }
}
