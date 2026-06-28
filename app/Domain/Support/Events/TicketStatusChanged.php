<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\TicketStatus;
use DateTimeImmutable;

class TicketStatusChanged
{
    public function __construct(
        public readonly TicketId $ticketId,
        public readonly TicketStatus $oldStatus,
        public readonly TicketStatus $newStatus,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
