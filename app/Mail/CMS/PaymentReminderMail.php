<?php

namespace App\Mail\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public string $reminderType, // 'upcoming', 'due_today'
        public ?int $daysUntilDue = null
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->reminderType) {
            'upcoming' => "Payment Reminder: Invoice {$this->invoice->invoice_number} Due Soon",
            'due_today' => "Payment Due Today: Invoice {$this->invoice->invoice_number}",
            default => "Payment Reminder: Invoice {$this->invoice->invoice_number}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cms.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->invoice->company,
                'customer' => $this->invoice->customer,
                'reminderType' => $this->reminderType,
                'daysUntilDue' => $this->daysUntilDue,
            ],
        );
    }
}
