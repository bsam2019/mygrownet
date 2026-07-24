<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Payment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly float $amount,
        public readonly string $paymentMethod,
        public readonly ?string $referenceNumber,
        public readonly ?string $receiptNumber,
        public readonly DateTimeImmutable $paymentDate,
        public readonly float $unallocatedAmount,
        public readonly bool $isVoided,
        public readonly ?string $voidReason,
        public readonly ?DateTimeImmutable $voidedAt,
        public readonly ?int $voidedBy,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            customerId: (int) $data['customer_id'],
            amount: (float) $data['amount'],
            paymentMethod: $data['payment_method'],
            referenceNumber: $data['reference_number'] ?? null,
            receiptNumber: $data['receipt_number'] ?? null,
            paymentDate: isset($data['payment_date']) ? new DateTimeImmutable($data['payment_date']) : new DateTimeImmutable(),
            unallocatedAmount: (float) ($data['unallocated_amount'] ?? 0),
            isVoided: (bool) ($data['is_voided'] ?? false),
            voidReason: $data['void_reason'] ?? null,
            voidedAt: isset($data['voided_at']) ? new DateTimeImmutable($data['voided_at']) : null,
            voidedBy: $data['voided_by'] ?? null,
            notes: $data['notes'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'customer_id' => $this->customerId,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'reference_number' => $this->referenceNumber,
            'receipt_number' => $this->receiptNumber,
            'payment_date' => $this->paymentDate->format('Y-m-d'),
            'unallocated_amount' => $this->unallocatedAmount,
            'is_voided' => $this->isVoided,
            'void_reason' => $this->voidReason,
            'voided_at' => $this->voidedAt?->format('Y-m-d H:i:s'),
            'voided_by' => $this->voidedBy,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
