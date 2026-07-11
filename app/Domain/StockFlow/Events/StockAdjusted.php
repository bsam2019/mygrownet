<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

use DateTimeImmutable;

class StockAdjusted implements DomainEvent
{
    public function __construct(
        private int $companyId,
        private int $itemId,
        private string $reason,
        private float $quantityBefore,
        private float $quantityAfter,
        private string $adjustmentType,
        private int $adjustedBy,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getItemId(): int { return $this->itemId; }
    public function getReason(): string { return $this->reason; }
    public function getQuantityBefore(): float { return $this->quantityBefore; }
    public function getQuantityAfter(): float { return $this->quantityAfter; }
    public function getAdjustmentType(): string { return $this->adjustmentType; }
}
