<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use DateTimeImmutable;

class EngagementCompleted
{
    public function __construct(
        public readonly EngagementId $engagementId,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
