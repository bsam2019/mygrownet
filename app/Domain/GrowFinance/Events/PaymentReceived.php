<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Events;

use DateTimeImmutable;

class PaymentReceived
{
    public function __construct(
        private int $companyId,
        private int $paymentId,
        private int $invoiceId,
        private string $invoiceNumber,
        private float $amount,
        private string $paymentMethod,
        private int $customerId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function getCompanyId(): int { return $this->companyId; }
    public function getPaymentId(): int { return $this->paymentId; }
    public function getInvoiceId(): int { return $this->invoiceId; }
    public function getInvoiceNumber(): string { return $this->invoiceNumber; }
    public function getAmount(): float { return $this->amount; }
    public function getPaymentMethod(): string { return $this->paymentMethod; }
    public function getCustomerId(): int { return $this->customerId; }
}
