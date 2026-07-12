<?php

namespace App\Notifications\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvestmentConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public VentureInvestmentModel $investment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $venture = $this->investment->venture;

        return (new MailMessage)
            ->subject("Investment Confirmed – {$venture->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your investment of K" . number_format($this->investment->amount, 2) . " in **{$venture->title}** has been confirmed.")
            ->line("Shares allocated: " . number_format($this->investment->shares_allocated ?? 0))
            ->action('View Investment', route('mygrownet.ventures.investment-details', $this->investment->id))
            ->line('Thank you for investing with MyGrowNet Venture Builder!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'venture_investment_confirmed',
            'investment_id' => $this->investment->id,
            'venture_id' => $this->investment->venture_id,
            'venture_title' => $this->investment->venture->title,
            'amount' => $this->investment->amount,
            'shares' => $this->investment->shares_allocated,
            'message' => "Your investment of K" . number_format($this->investment->amount, 2) . " in {$this->investment->venture->title} has been confirmed.",
        ];
    }
}
