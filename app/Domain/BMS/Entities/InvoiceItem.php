<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class InvoiceItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $invoiceId,
        public readonly string $description,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly float $lineTotal,
        public readonly ?float $amount,
        public readonly ?string $dimensions,
        public readonly ?float $dimensionsValue,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            invoiceId: (int) $data['invoice_id'],
            description: $data['description'],
            quantity: (float) ($data['quantity'] ?? 0),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            lineTotal: (float) ($data['line_total'] ?? 0),
            amount: array_key_exists('amount', $data) ? (float) $data['amount'] : null,
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
            'invoice_id' => $this->invoiceId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
            'amount' => $this->amount,
            'dimensions' => $this->dimensions,
            'dimensions_value' => $this->dimensionsValue,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
