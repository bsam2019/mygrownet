<?php

namespace App\Notifications\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VentureStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public VentureModel $venture,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'venture_status_changed',
            'venture_id' => $this->venture->id,
            'venture_title' => $this->venture->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "{$this->venture->title} status changed from {$this->oldStatus} to {$this->newStatus}.",
        ];
    }
}
