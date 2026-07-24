<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Quotation
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly string $quotationNumber,
        public readonly DateTimeImmutable $quotationDate,
        public readonly ?DateTimeImmutable $expiryDate,
        public readonly float $subtotal,
        public readonly float $taxAmount,
        public readonly float $discountAmount,
        public readonly float $totalAmount,
        public readonly string $status,
        public readonly ?string $notes,
        public readonly ?string $terms,
        public readonly ?int $convertedToJobId,
        public readonly ?int $branchId,
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
            quotationNumber: $data['quotation_number'],
            quotationDate: isset($data['quotation_date']) ? new DateTimeImmutable($data['quotation_date']) : new DateTimeImmutable(),
            expiryDate: isset($data['expiry_date']) ? new DateTimeImmutable($data['expiry_date']) : null,
            subtotal: (float) ($data['subtotal'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            status: $data['status'] ?? 'draft',
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
            convertedToJobId: $data['converted_to_job_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isConverted(): bool
    {
        return $this->convertedToJobId !== null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'customer_id' => $this->customerId,
            'quotation_number' => $this->quotationNumber,
            'quotation_date' => $this->quotationDate->format('Y-m-d'),
            'expiry_date' => $this->expiryDate?->format('Y-m-d'),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discountAmount,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'converted_to_job_id' => $this->convertedToJobId,
            'branch_id' => $this->branchId,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
