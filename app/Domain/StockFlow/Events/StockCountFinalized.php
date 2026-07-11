<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

use DateTimeImmutable;

class StockCountFinalized implements DomainEvent
{
    public function __construct(
        private int $companyId,
        private int $physicalCountId,
        private int $finalizedBy,
        private array $totals, // [total_system_value, total_physical_value, total_variance]
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getPhysicalCountId(): int { return $this->physicalCountId; }
    public function getFinalizedBy(): int { return $this->finalizedBy; }
    public function getTotals(): array { return $this->totals; }
}
