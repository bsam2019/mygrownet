<?php

namespace App\Notifications\GrowBiz;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $taskId,
        private string $taskTitle,
        private string $assignerName,
        private ?string $dueDate = null
    ) {}

    public function via(object $notifiable): array
    {
        // Use database channel like the main app
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New Task Assigned: ' . $this->taskTitle)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned a new task.')
            ->line('**Task:** ' . $this->taskTitle)
            ->line('**Assigned by:** ' . $this->assignerName);

        if ($this->dueDate) {
            $mail->line('**Due:** ' . $this->dueDate);
        }

        return $mail
            ->action('View Task', url('/growbiz/tasks/' . $this->taskId))
            ->line('Thank you for using GrowBiz!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'growbiz_task_assigned',
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'assigner_name' => $this->assignerName,
            'due_date' => $this->dueDate,
            'message' => "{$this->assignerName} assigned you a task: {$this->taskTitle}",
            'url' => '/growbiz/tasks/' . $this->taskId,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
