<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $withdrawal;

    public function __construct(Transaction $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Approved')
            ->line('Your withdrawal request has been approved.')
            ->line('Amount: ' . number_format($this->withdrawal->amount, 2) . ' ZMW')
            ->line('Reference: ' . $this->withdrawal->reference_number)
            ->action('View Details', route('user.withdrawals.show', $this->withdrawal))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'reference_number' => $this->withdrawal->reference_number,
            'type' => 'withdrawal_approved'
        ];
    }
} 