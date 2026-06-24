<?php

namespace App\Notifications\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VentureFundedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public VentureModel $venture,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Funding Complete – {$this->venture->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Great news! **{$this->venture->title}** has reached its funding target of K" . number_format($this->venture->funding_target, 2) . ".")
            ->line("Total raised: K" . number_format($this->venture->total_raised, 2) . " from {$this->venture->investor_count} investors.")
            ->line("The venture will now move to company formation and operations.")
            ->action('View Venture', route('ventures.show', $this->venture->slug))
            ->line('Thank you for being part of this investment!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'venture_funded',
            'venture_id' => $this->venture->id,
            'venture_title' => $this->venture->title,
            'total_raised' => $this->venture->total_raised,
            'message' => "{$this->venture->title} has been fully funded!",
        ];
    }
}
