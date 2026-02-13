<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $payment
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received - ' . $this->payment['reference_number'])
            ->greeting('Hello ' . $this->payment['customer_name'])
            ->line('We have received your payment.')
            ->line('Amount: K' . number_format($this->payment['amount'], 2))
            ->line('Payment Method: ' . ucwords(str_replace('_', ' ', $this->payment['payment_method'])))
            ->line('Reference: ' . $this->payment['reference_number'])
            ->action('View Receipt', url('/cms/payments/' . $this->payment['id']))
            ->line('Thank you for your payment!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'payment_received',
            'title' => 'Payment Received',
            'message' => 'Payment of K' . number_format($this->payment['amount'], 2) . ' received from ' . $this->payment['customer_name'],
            'payment_id' => $this->payment['id'],
            'reference_number' => $this->payment['reference_number'],
            'amount' => $this->payment['amount'],
            'customer_name' => $this->payment['customer_name'],
            'payment_method' => $this->payment['payment_method'],
            'url' => '/cms/payments/' . $this->payment['id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
