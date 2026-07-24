<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Contract
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly ?int $customerId,
        public readonly ?int $vendorId,
        public readonly string $contractNumber,
        public readonly string $title,
        public readonly ?string $description,
        public readonly DateTimeImmutable $startDate,
        public readonly ?DateTimeImmutable $endDate,
        public readonly ?float $contractValue,
        public readonly string $status,
        public readonly ?string $terms,
        public readonly ?string $notes,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            customerId: $data['customer_id'] ?? null,
            vendorId: $data['vendor_id'] ?? null,
            contractNumber: $data['contract_number'] ?? '',
            title: $data['title'],
            description: $data['description'] ?? null,
            startDate: isset($data['start_date']) ? new DateTimeImmutable($data['start_date']) : new DateTimeImmutable(),
            endDate: isset($data['end_date']) ? new DateTimeImmutable($data['end_date']) : null,
            contractValue: array_key_exists('contract_value', $data) ? (float) $data['contract_value'] : null,
            status: $data['status'] ?? 'draft',
            terms: $data['terms'] ?? null,
            notes: $data['notes'] ?? null,
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
            'vendor_id' => $this->vendorId,
            'contract_number' => $this->contractNumber,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'contract_value' => $this->contractValue,
            'status' => $this->status,
            'terms' => $this->terms,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
