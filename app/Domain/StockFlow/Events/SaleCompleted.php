<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Events;

use DateTimeImmutable;

class SaleCompleted implements DomainEvent
{
    public function __construct(
        private int $companyId,
        private int $saleId,
        private string $receiptNumber,
        private float $total,
        private string $paymentMethod,
        private int $soldBy,
        private array $items, // [[item_id, quantity, unit_price, line_total], ...]
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getSaleId(): int { return $this->saleId; }
    public function getReceiptNumber(): string { return $this->receiptNumber; }
    public function getTotal(): float { return $this->total; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getSoldBy(): int { return $this->soldBy; }
    public function getItems(): array { return $this->items; }
}
