<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;

class TransactionNotificationMail extends BrandedMail
{
    public function __construct(
        public string $title,
        public string $greeting,
        public string $message,
        public array $transactionDetails,
        public string $status = 'success',
        public ?string $statusMessage = null,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
    ) {
        parent::__construct();
        $this->subject($title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transaction-notification',
            with: [
                'title' => $this->title,
                'greeting' => $this->greeting,
                'message' => $this->message,
                'transactionDetails' => $this->transactionDetails,
                'status' => $this->status,
                'statusMessage' => $this->statusMessage,
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
            ],
        );
    }
}
