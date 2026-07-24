<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class PaymentAllocation
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $paymentId,
        public readonly int $invoiceId,
        public readonly float $amount,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            paymentId: (int) $data['payment_id'],
            invoiceId: (int) $data['invoice_id'],
            amount: (float) $data['amount'],
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->paymentId,
            'invoice_id' => $this->invoiceId,
            'amount' => $this->amount,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
