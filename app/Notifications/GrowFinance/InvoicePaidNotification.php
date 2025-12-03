<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $invoiceId,
        private string $invoiceNumber,
        private string $customerName,
        private float $amountPaid,
        private float $totalAmount,
        private bool $isFullyPaid = true
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->isFullyPaid ? 'Fully Paid' : 'Partial Payment';
        
        return (new MailMessage)
            ->subject("Invoice {$status}: {$this->invoiceNumber}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Payment received for invoice {$this->invoiceNumber}.")
            ->line('**Customer:** ' . $this->customerName)
            ->line('**Amount Paid:** K' . number_format($this->amountPaid, 2))
            ->line('**Invoice Total:** K' . number_format($this->totalAmount, 2))
            ->line('**Status:** ' . $status)
            ->action('View Invoice', url('/growfinance/invoices/' . $this->invoiceId))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        $status = $this->isFullyPaid ? 'Fully Paid' : 'Partial Payment';
        
        return [
            'title' => "Invoice {$status}",
            'message' => "Invoice {$this->invoiceNumber} - K" . number_format($this->amountPaid, 2) . " received from {$this->customerName}",
            'type' => 'success',
            'module' => 'growfinance',
            'category' => 'payments',
            'action_url' => '/growfinance/invoices/' . $this->invoiceId,
            'action_text' => 'View Invoice',
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'customer_name' => $this->customerName,
            'amount_paid' => $this->amountPaid,
            'total_amount' => $this->totalAmount,
            'is_fully_paid' => $this->isFullyPaid,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
