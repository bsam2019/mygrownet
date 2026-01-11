<?php

namespace App\Notifications\GrowBuilder;

use App\Infrastructure\GrowBuilder\Models\SiteContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SiteContactMessage $message,
        public string $siteName,
        public string $siteUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Contact Message - {$this->siteName}")
            ->greeting("New message from {$this->message->name}")
            ->line("You have received a new contact form submission on {$this->siteName}.")
            ->line("**From:** {$this->message->name}")
            ->line("**Email:** {$this->message->email}")
            ->line($this->message->phone ? "**Phone:** {$this->message->phone}" : '')
            ->line($this->message->subject ? "**Subject:** {$this->message->subject}" : '')
            ->line("**Message:**")
            ->line($this->message->message)
            ->action('View Message', $this->siteUrl . '/dashboard/messages/' . $this->message->id)
            ->line('Thank you for using GrowBuilder!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'contact_form_submitted',
            'site_name' => $this->siteName,
            'message_id' => $this->message->id,
            'sender_name' => $this->message->name,
            'sender_email' => $this->message->email,
            'subject' => $this->message->subject,
        ];
    }
}
