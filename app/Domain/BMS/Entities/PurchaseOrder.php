<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class PurchaseOrder
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly ?int $vendorId,
        public readonly string $orderNumber,
        public readonly string $status,
        public readonly DateTimeImmutable $orderDate,
        public readonly ?DateTimeImmutable $expectedDelivery,
        public readonly float $subtotal,
        public readonly float $taxAmount,
        public readonly float $totalAmount,
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
            vendorId: $data['vendor_id'] ?? null,
            orderNumber: $data['order_number'] ?? '',
            status: $data['status'] ?? 'draft',
            orderDate: isset($data['order_date']) ? new DateTimeImmutable($data['order_date']) : new DateTimeImmutable(),
            expectedDelivery: isset($data['expected_delivery']) ? new DateTimeImmutable($data['expected_delivery']) : null,
            subtotal: (float) ($data['subtotal'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
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
            'vendor_id' => $this->vendorId,
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'order_date' => $this->orderDate->format('Y-m-d'),
            'expected_delivery' => $this->expectedDelivery?->format('Y-m-d'),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'total_amount' => $this->totalAmount,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
