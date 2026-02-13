<?php

namespace App\Mail\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentModel $payment,
        public ?string $receiptPath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Received - Receipt #{$this->payment->receipt_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cms.payment-received',
            with: [
                'payment' => $this->payment,
                'company' => $this->payment->company,
                'customer' => $this->payment->customer,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->receiptPath && file_exists($this->receiptPath)) {
            $attachments[] = Attachment::fromPath($this->receiptPath)
                ->as("Receipt-{$this->payment->receipt_number}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
