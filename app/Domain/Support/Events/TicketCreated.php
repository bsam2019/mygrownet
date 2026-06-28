<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketCategory;
use App\Domain\Support\ValueObjects\TicketPriority;
use DateTimeImmutable;

class TicketCreated
{
    public function __construct(
        public readonly TicketId $ticketId,
        public readonly UserId $userId,
        public readonly TicketCategory $category,
        public readonly TicketPriority $priority,
        public readonly string $subject,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
