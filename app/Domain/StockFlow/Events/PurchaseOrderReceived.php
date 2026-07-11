<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

use DateTimeImmutable;

class PurchaseOrderReceived implements DomainEvent
{
    public function __construct(
        private int $companyId,
        private int $purchaseOrderId,
        private string $orderNumber,
        private int $receivedBy,
        private array $items, // [[item_id, quantity, unit_cost], ...]
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getPurchaseOrderId(): int { return $this->purchaseOrderId; }
    public function getOrderNumber(): string { return $this->orderNumber; }
    public function getReceivedBy(): int { return $this->receivedBy; }
    public function getItems(): array { return $this->items; }
}
