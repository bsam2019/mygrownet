<?php

namespace App\Notifications\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DividendPaidNotification extends Notification
{
    use Queueable;

    public function __construct(
        public VentureDividendModel $dividend,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $venture = $this->dividend->venture;

        return (new MailMessage)
            ->subject("Dividend Paid – {$venture->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A dividend payment of K" . number_format($this->dividend->amount, 2) . " has been paid to your wallet.")
            ->line("Period: {$this->dividend->dividend_period}")
            ->line("Venture: {$venture->title}")
            ->action('View Dividends', route('mygrownet.ventures.dividends'))
            ->line('Thank you for being a shareholder!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'venture_dividend_paid',
            'dividend_id' => $this->dividend->id,
            'venture_id' => $this->dividend->venture_id,
            'venture_title' => $this->dividend->venture->title,
            'amount' => $this->dividend->amount,
            'period' => $this->dividend->dividend_period,
            'message' => "Dividend of K" . number_format($this->dividend->amount, 2) . " paid for {$this->dividend->dividend_period}.",
        ];
    }
}
