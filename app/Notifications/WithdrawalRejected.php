<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $withdrawal;
    protected $reason;

    public function __construct(Transaction $withdrawal, string $reason)
    {
        $this->withdrawal = $withdrawal;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Rejected')
            ->line('Your withdrawal request has been rejected.')
            ->line('Amount: ' . number_format($this->withdrawal->amount, 2) . ' ZMW')
            ->line('Reference: ' . $this->withdrawal->reference_number)
            ->line('Reason: ' . $this->reason)
            ->action('View Details', route('user.withdrawals.show', $this->withdrawal))
            ->line('If you have any questions, please contact our support team.');
    }

    public function toArray($notifiable): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'reference_number' => $this->withdrawal->reference_number,
            'reason' => $this->reason,
            'type' => 'withdrawal_rejected'
        ];
    }
} 