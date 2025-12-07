<?php

namespace App\Notifications\BizBoost;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $customerName,
        public string $reminderType,
        public string $businessName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("BizBoost Reminder: {$this->title}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("You have a follow-up reminder for your business **{$this->businessName}**:")
            ->line("**{$this->title}**");

        if ($this->description) {
            $message->line($this->description);
        }

        if ($this->customerName) {
            $message->line("Customer: {$this->customerName}");
        }

        $message->action('View Reminders', route('bizboost.reminders.index'))
            ->line('Stay on top of your customer relationships!');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bizboost_reminder',
            'title' => $this->title,
            'description' => $this->description,
            'customer_name' => $this->customerName,
            'reminder_type' => $this->reminderType,
            'business_name' => $this->businessName,
        ];
    }
}
