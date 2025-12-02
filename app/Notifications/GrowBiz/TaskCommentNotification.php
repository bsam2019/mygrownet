<?php

namespace App\Notifications\GrowBiz;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $taskId,
        private string $taskTitle,
        private string $commenterName,
        private string $commentPreview
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Comment on: ' . $this->taskTitle)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->commenterName . ' commented on a task you\'re involved with.')
            ->line('**Task:** ' . $this->taskTitle)
            ->line('"' . $this->commentPreview . '"')
            ->action('View Task', url('/growbiz/tasks/' . $this->taskId))
            ->line('Thank you for using GrowBiz!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'growbiz_task_comment',
            'task_id' => $this->taskId,
            'task_title' => $this->taskTitle,
            'commenter_name' => $this->commenterName,
            'comment_preview' => $this->commentPreview,
            'message' => "{$this->commenterName} commented on: {$this->taskTitle}",
            'url' => '/growbiz/tasks/' . $this->taskId,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
