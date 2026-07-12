<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class ComplianceDeadlineMissed
{
    public function __construct(
        public readonly ComplianceTaskId $taskId,
        public readonly ClientId $clientId,
        public readonly string $complianceType,
        public readonly string $dueDate,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
