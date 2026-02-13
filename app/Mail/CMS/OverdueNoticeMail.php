<?php

namespace App\Mail\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public int $daysOverdue
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "OVERDUE: Invoice {$this->invoice->invoice_number} - {$this->daysOverdue} Days Past Due",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cms.overdue-notice',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->invoice->company,
                'customer' => $this->invoice->customer,
                'daysOverdue' => $this->daysOverdue,
            ],
        );
    }
}
