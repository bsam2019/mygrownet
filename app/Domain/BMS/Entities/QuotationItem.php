<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class QuotationItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $quotationId,
        public readonly string $description,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly float $lineTotal,
        public readonly ?float $taxRate,
        public readonly ?float $discountRate,
        public readonly ?string $dimensions,
        public readonly ?float $dimensionsValue,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            quotationId: (int) $data['quotation_id'],
            description: $data['description'],
            quantity: (float) ($data['quantity'] ?? 0),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            lineTotal: (float) ($data['line_total'] ?? 0),
            taxRate: array_key_exists('tax_rate', $data) ? (float) $data['tax_rate'] : null,
            discountRate: array_key_exists('discount_rate', $data) ? (float) $data['discount_rate'] : null,
            dimensions: $data['dimensions'] ?? null,
            dimensionsValue: array_key_exists('dimensions_value', $data) ? (float) $data['dimensions_value'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quotation_id' => $this->quotationId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
            'tax_rate' => $this->taxRate,
            'discount_rate' => $this->discountRate,
            'dimensions' => $this->dimensions,
            'dimensions_value' => $this->dimensionsValue,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
