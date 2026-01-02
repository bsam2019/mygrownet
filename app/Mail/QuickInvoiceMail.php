<?php

namespace App\Mail;

use App\Domain\QuickInvoice\Entities\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuickInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Document $document,
        public readonly string $pdfUrl,
        public readonly ?string $customMessage = null
    ) {}

    public function envelope(): Envelope
    {
        $type = $this->document->type()->label();
        $number = $this->document->documentNumber();
        $businessName = $this->document->businessInfo()->name();

        return new Envelope(
            subject: "{$type} #{$number} from {$businessName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quick-invoice',
            with: [
                'document' => $this->document->toArray(),
                'pdfUrl' => $this->pdfUrl,
                'customMessage' => $this->customMessage,
            ],
        );
    }
}
