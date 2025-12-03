<?php

namespace App\Notifications\GrowFinance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $invoiceId,
        private string $invoiceNumber,
        private string $customerName,
        private float $amountDue,
        private string $dueDate,
        private int $daysOverdue
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Invoice Overdue: {$this->invoiceNumber}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Invoice {$this->invoiceNumber} is now overdue.")
            ->line('**Customer:** ' . $this->customerName)
            ->line('**Amount Due:** K' . number_format($this->amountDue, 2))
            ->line('**Due Date:** ' . $this->dueDate)
            ->line('**Days Overdue:** ' . $this->daysOverdue)
            ->line('Consider following up with the customer for payment.')
            ->action('View Invoice', url('/growfinance/invoices/' . $this->invoiceId))
            ->line('Thank you for using GrowFinance!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Invoice Overdue',
            'message' => "Invoice {$this->invoiceNumber} is {$this->daysOverdue} days overdue - K" . number_format($this->amountDue, 2) . " from {$this->customerName}",
            'type' => 'warning',
            'module' => 'growfinance',
            'category' => 'invoices',
            'priority' => 'high',
            'action_url' => '/growfinance/invoices/' . $this->invoiceId,
            'action_text' => 'View Invoice',
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'customer_name' => $this->customerName,
            'amount_due' => $this->amountDue,
            'due_date' => $this->dueDate,
            'days_overdue' => $this->daysOverdue,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
