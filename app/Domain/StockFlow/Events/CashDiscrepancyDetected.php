<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

use DateTimeImmutable;

class CashDiscrepancyDetected implements DomainEvent
{
    public function __construct(
        private int $companyId,
        private int $cashRegisterId,
        private string $registerDate,
        private float $expectedAmount,
        private float $countedAmount,
        private float $variance,
        private int $closedBy,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getCashRegisterId(): int { return $this->cashRegisterId; }
    public function getExpectedAmount(): float { return $this->expectedAmount; }
    public function getCountedAmount(): float { return $this->countedAmount; }
    public function getVariance(): float { return $this->variance; }
}
