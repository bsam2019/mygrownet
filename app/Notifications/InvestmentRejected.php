<?php

namespace App\Notifications;

use App\Models\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvestmentRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $investment;

    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Investment Rejected - MyGrowNet')
            ->view('emails.notification', [
                'greeting' => 'Hello ' . $notifiable->name,
                'introLines' => [
                    'Your investment request has been rejected.',
                    'Investment Details:',
                    'Amount: K' . number_format($this->investment->amount, 2),
                    'Category: ' . $this->investment->category->name,
                    'Reason: ' . $this->investment->rejection_reason,
                ],
                'actionText' => 'View Investment',
                'actionUrl' => route('investments.show', $this->investment),
                'outroLines' => [
                    'If you have any questions, please contact our support team.',
                ],
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'investment_id' => $this->investment->id,
            'amount' => $this->investment->amount,
            'category' => $this->investment->category->name,
            'reason' => $this->investment->rejection_reason,
            'type' => 'investment_rejected'
        ];
    }
}