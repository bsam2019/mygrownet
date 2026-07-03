<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class SendBulkEmails implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public array $recipients, public string $subject, public string $content) {}

    public function handle(): void {}
}