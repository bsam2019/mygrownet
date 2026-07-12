<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\EngagementType;
use DateTimeImmutable;

class EngagementStarted
{
    public function __construct(
        public readonly EngagementId $engagementId,
        public readonly ClientId $clientId,
        public readonly EngagementType $type,
        public readonly string $description,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
