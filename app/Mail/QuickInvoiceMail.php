<?php

namespace App\Mail;

use App\Domain\QuickInvoice\Entities\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuickInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Document $document,
        public readonly string $pdfContent, // Raw PDF content for attachment
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
                'customMessage' => $this->customMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $type = str_replace('_', '-', $this->document->type()->value);
        $number = $this->document->documentNumber()->value();
        $filename = "{$type}-{$number}.pdf";

        return [
            Attachment::fromData(fn () => $this->pdfContent, $filename)
                ->withMime('application/pdf'),
        ];
    }
}
