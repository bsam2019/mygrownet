<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $invoiceId,
        private string $invoiceNumber,
        private string $customerName,
        private float $totalAmount,
        private string $dueDate
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice Created: ' . $this->invoiceNumber)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new invoice has been created.')
            ->line('**Invoice:** ' . $this->invoiceNumber)
            ->line('**Customer:** ' . $this->customerName)
            ->line('**Amount:** K' . number_format($this->totalAmount, 2))
            ->line('**Due:** ' . $this->dueDate)
            ->action('View Invoice', url('/growfinance/invoices/' . $this->invoiceId))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Invoice Created',
            'message' => "Invoice {$this->invoiceNumber} created for {$this->customerName} - K" . number_format($this->totalAmount, 2),
            'type' => 'info',
            'module' => 'growfinance',
            'category' => 'invoices',
            'action_url' => '/growfinance/invoices/' . $this->invoiceId,
            'action_text' => 'View Invoice',
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'customer_name' => $this->customerName,
            'total_amount' => $this->totalAmount,
            'due_date' => $this->dueDate,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
