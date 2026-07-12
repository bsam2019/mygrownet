<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\AppointmentId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class AppointmentScheduled
{
    public function __construct(
        public readonly AppointmentId $appointmentId,
        public readonly ClientId $clientId,
        public readonly string $startTime,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
