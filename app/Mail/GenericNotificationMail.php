<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;

class GenericNotificationMail extends BrandedMail
{
    public function __construct(
        string $subject,
        public string $greeting,
        public string $message,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public ?array $details = null,
        public ?string $infoBox = null,
        public ?string $infoBoxType = null,
        public ?string $footerNote = null,
    ) {
        parent::__construct();
        $this->subject = $subject;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic',
            with: [
                'greeting' => $this->greeting,
                'message' => $this->message,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
                'details' => $this->details,
                'infoBox' => $this->infoBox,
                'infoBoxType' => $this->infoBoxType,
                'footerNote' => $this->footerNote,
            ],
        );
    }
}
