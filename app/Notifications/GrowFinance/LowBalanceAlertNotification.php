<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $accountId,
        private string $accountName,
        private float $currentBalance,
        private float $threshold
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Low Balance Alert: {$this->accountName}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your account balance is running low.")
            ->line('**Account:** ' . $this->accountName)
            ->line('**Current Balance:** K' . number_format($this->currentBalance, 2))
            ->line('**Alert Threshold:** K' . number_format($this->threshold, 2))
            ->line('Consider reviewing your cash flow and upcoming expenses.')
            ->action('View Banking', url('/growfinance/banking'))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Low Balance Alert',
            'message' => "{$this->accountName} has K" . number_format($this->currentBalance, 2) . " (below K" . number_format($this->threshold, 2) . " threshold)",
            'type' => 'warning',
            'module' => 'growfinance',
            'category' => 'banking',
            'priority' => 'high',
            'action_url' => '/growfinance/banking',
            'action_text' => 'View Banking',
            'account_id' => $this->accountId,
            'account_name' => $this->accountName,
            'current_balance' => $this->currentBalance,
            'threshold' => $this->threshold,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
