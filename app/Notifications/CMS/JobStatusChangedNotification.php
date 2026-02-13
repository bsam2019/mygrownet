<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class JobStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $job,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = ucwords(str_replace('_', ' ', $this->newStatus));
        
        return (new MailMessage)
            ->subject('Job Status Updated - ' . $this->job['job_number'])
            ->greeting('Hello ' . $this->job['customer_name'])
            ->line('The status of your job has been updated.')
            ->line('Job Number: ' . $this->job['job_number'])
            ->line('Job Type: ' . $this->job['job_type'])
            ->line('New Status: ' . $statusLabel)
            ->when($this->newStatus === 'completed', function ($mail) {
                return $mail->line('Your job has been completed successfully!');
            })
            ->action('View Job Details', url('/cms/jobs/' . $this->job['id']))
            ->line('Thank you for your business!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'job_status_changed',
            'title' => 'Job Status Updated',
            'message' => 'Job ' . $this->job['job_number'] . ' status changed to ' . ucwords(str_replace('_', ' ', $this->newStatus)),
            'job_id' => $this->job['id'],
            'job_number' => $this->job['job_number'],
            'job_type' => $this->job['job_type'],
            'customer_name' => $this->job['customer_name'],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'url' => '/cms/jobs/' . $this->job['id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
