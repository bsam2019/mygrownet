<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Events;

class PaymentReceived
{
    public const NAME = 'growfinance.payment.received.v1';

    public function __construct(
        public readonly int $companyId,
        public readonly int $paymentId,
        public readonly int $invoiceId,
        public readonly string $invoiceNumber,
        public readonly float $amount,
        public readonly string $paymentMethod,
        public readonly int $customerId,
        public readonly \DateTimeImmutable $occurredAt,
    ) {}

    public function toPayload(): array
    {
        return [
            'company_id' => $this->companyId,
            'payment_id' => $this->paymentId,
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'customer_id' => $this->customerId,
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
