<?php

namespace App\Notifications\GrowBiz;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $taskId,
        private string $taskTitle,
        private string $dueDate,
        private bool $isOverdue = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isOverdue 
            ? 'Overdue Task: ' . $this->taskTitle
            : 'Task Due Soon: ' . $this->taskTitle;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!');

        if ($this->isOverdue) {
            $mail->line('You have an overdue task that needs attention.')
                ->line('**Task:** ' . $this->taskTitle)
                ->line('**Was due:** ' . $this->dueDate);
        } else {
            $mail->line('You have a task due soon.')
                ->line('**Task:** ' . $this->taskTitle)
                ->line('**Due:** ' . $this->dueDate);
        }

        return $mail
            ->action('View Task', url('/growbiz/tasks/' . $this->taskId))
            ->line('Thank you for using GrowBiz!');
    }

    public function toDatabase(object $notifiable): array
    {
        $message = $this->isOverdue
            ? "Task \"{$this->taskTitle}\" is overdue (was due {$this->dueDate})"
            : "Task \"{$this->taskTitle}\" is due on {$this->dueDate}";

        return [
            'type' => $this->isOverdue ? 'growbiz_task_overdue' : 'growbiz_task_due_reminder',
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'due_date' => $this->dueDate,
            'is_overdue' => $this->isOverdue,
            'message' => $message,
            'url' => '/growbiz/tasks/' . $this->taskId,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
