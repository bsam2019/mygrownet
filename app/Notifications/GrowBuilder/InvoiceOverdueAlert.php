<?php

namespace App\Notifications\GrowBuilder;

use App\Models\AgencyClientInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AgencyClientInvoice $invoice,
        public int $daysOverdue
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('growbuilder.invoices.show', $this->invoice->id);

        $urgency = match(true) {
            $this->daysOverdue >= 30 => 'URGENT',
            $this->daysOverdue >= 14 => 'Important',
            default => 'Reminder'
        };

        return (new MailMessage)
            ->subject("{$urgency}: Invoice {$this->invoice->invoice_number} is Overdue")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Invoice {$this->invoice->invoice_number} is now {$this->daysOverdue} days overdue.")
            ->line("**Client:** {$this->invoice->client->client_name}")
            ->line("**Invoice Date:** {$this->invoice->invoice_date->format('M d, Y')}")
            ->line("**Due Date:** {$this->invoice->due_date->format('M d, Y')}")
            ->line("**Outstanding Balance:** {$this->invoice->currency} " . number_format($this->invoice->balance, 2))
            ->action('View Invoice', $url)
            ->line('Please follow up with your client to collect payment.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_overdue_alert',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'client_id' => $this->invoice->client_id,
            'client_name' => $this->invoice->client->client_name,
            'due_date' => $this->invoice->due_date->format('Y-m-d'),
            'days_overdue' => $this->daysOverdue,
            'balance' => $this->invoice->balance,
            'currency' => $this->invoice->currency,
            'url' => route('growbuilder.invoices.show', $this->invoice->id),
        ];
    }
}
