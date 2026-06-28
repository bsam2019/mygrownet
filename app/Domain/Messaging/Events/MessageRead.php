<?php

namespace App\Domain\Messaging\Events;

use App\Domain\Messaging\Entities\Message;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Message $message
    ) {}
}
