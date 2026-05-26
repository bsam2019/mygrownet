<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Base class for all MyGrowNet branded emails
 * 
 * Provides consistent branding, styling, and structure for all outgoing emails.
 * All application emails should extend this class.
 */
abstract class BrandedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Email greeting (e.g., "Hello John")
     */
    public ?string $greeting = null;

    /**
     * Main email content/message
     */
    public ?string $message = null;

    /**
     * Call-to-action button text
     */
    public ?string $actionText = null;

    /**
     * Call-to-action button URL
     */
    public ?string $actionUrl = null;

    /**
     * Secondary button text
     */
    public ?string $secondaryActionText = null;

    /**
     * Secondary button URL
     */
    public ?string $secondaryActionUrl = null;

    /**
     * Additional data to pass to the view
     */
    public array $data = [];

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->from(
            config('mail.from.address'),
            config('mail.from.name')
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject ?? 'MyGrowNet Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    abstract public function content(): Content;

    /**
     * Set the email greeting
     */
    public function withGreeting(string $greeting): static
    {
        $this->greeting = $greeting;
        return $this;
    }

    /**
     * Set the email message
     */
    public function withMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the primary action button
     */
    public function withAction(string $text, string $url): static
    {
        $this->actionText = $text;
        $this->actionUrl = $url;
        return $this;
    }

    /**
     * Set the secondary action button
     */
    public function withSecondaryAction(string $text, string $url): static
    {
        $this->secondaryActionText = $text;
        $this->secondaryActionUrl = $url;
        return $this;
    }

    /**
     * Add additional data to the email
     */
    public function withData(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
