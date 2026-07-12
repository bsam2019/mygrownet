<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class ClientRegistered
{
    public function __construct(
        public readonly ClientId $clientId,
        public readonly string $name,
        public readonly string $email,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
