<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SaleRecordedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $saleId,
        private string $description,
        private float $amount,
        private string $paymentMethod,
        private ?string $customerName = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('💰 Sale Recorded: K' . number_format($this->amount, 2))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new sale has been recorded.')
            ->line('**Description:** ' . $this->description)
            ->line('**Amount:** K' . number_format($this->amount, 2))
            ->line('**Payment Method:** ' . ucfirst(str_replace('_', ' ', $this->paymentMethod)));

        if ($this->customerName) {
            $mail->line('**Customer:** ' . $this->customerName);
        }

        return $mail
            ->action('View Sales', url('/growfinance/sales'))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        $message = "{$this->description} - K" . number_format($this->amount, 2);
        if ($this->customerName) {
            $message .= " from {$this->customerName}";
        }

        return [
            'title' => 'Sale Recorded',
            'message' => $message,
            'type' => 'success',
            'module' => 'growfinance',
            'category' => 'sales',
            'action_url' => '/growfinance/sales',
            'action_text' => 'View Sales',
            'sale_id' => $this->saleId,
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'customer_name' => $this->customerName,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
