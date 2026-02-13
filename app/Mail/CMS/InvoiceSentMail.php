<?php

namespace App\Mail\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public ?string $pdfPath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice {$this->invoice->invoice_number} from {$this->invoice->company->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cms.invoice-sent',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->invoice->company,
                'customer' => $this->invoice->customer,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as("Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
