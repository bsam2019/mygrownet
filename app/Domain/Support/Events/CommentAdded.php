<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use DateTimeImmutable;

class CommentAdded
{
    public function __construct(
        public readonly TicketId $ticketId,
        public readonly UserId $userId,
        public readonly bool $isInternal,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
