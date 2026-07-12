<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MarketingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $subject, public string $content) {}

    public function build(): self
    {
        return $this->subject($this->subject)->html($this->content);
    }
}