<?php

namespace App\Notifications\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $invoice
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice ' . $this->invoice['invoice_number'] . ' Sent')
            ->greeting('Hello ' . $this->invoice['customer_name'])
            ->line('An invoice has been sent to you.')
            ->line('Invoice Number: ' . $this->invoice['invoice_number'])
            ->line('Amount: K' . number_format($this->invoice['total_amount'], 2))
            ->line('Due Date: ' . $this->invoice['due_date'])
            ->action('View Invoice', url('/cms/invoices/' . $this->invoice['id']))
            ->line('Thank you for your business!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'invoice_sent',
            'title' => 'Invoice Sent',
            'message' => 'Invoice ' . $this->invoice['invoice_number'] . ' sent to ' . $this->invoice['customer_name'],
            'invoice_id' => $this->invoice['id'],
            'invoice_number' => $this->invoice['invoice_number'],
            'amount' => $this->invoice['total_amount'],
            'customer_name' => $this->invoice['customer_name'],
            'url' => '/cms/invoices/' . $this->invoice['id'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
