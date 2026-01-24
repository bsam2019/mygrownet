<?php

namespace App\Notifications\Ubumi;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckInAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $message,
        private readonly string $personName
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ubumi Alert: ' . $this->personName)
            ->line($this->message)
            ->line('Please check on them to ensure they are okay.')
            ->action('View in Ubumi', url('/ubumi'))
            ->line('This is an automated alert from your Ubumi family wellness system.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'person_name' => $this->personName,
            'type' => 'check_in_alert',
        ];
    }
}
