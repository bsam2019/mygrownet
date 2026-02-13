<?php

namespace App\Mail\CMS;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuspiciousActivityAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $activity,
        public string $companyName
    ) {}

    public function build()
    {
        return $this->subject("Security Alert: Suspicious Activity Detected - {$this->companyName}")
            ->view('emails.cms.suspicious-activity-alert');
    }
}
