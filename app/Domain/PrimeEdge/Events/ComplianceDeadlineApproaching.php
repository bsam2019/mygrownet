<?php

namespace App\Domain\PrimeEdge\Events;

use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class ComplianceDeadlineApproaching
{
    public function __construct(
        public readonly ComplianceTaskId $taskId,
        public readonly ClientId $clientId,
        public readonly string $complianceType,
        public readonly string $dueDate,
        public readonly int $daysUntilDue,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
