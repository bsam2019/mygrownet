<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class PurchaseOrderItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $purchaseOrderId,
        public readonly string $description,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly float $lineTotal,
        public readonly ?float $quantityReceived,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            purchaseOrderId: (int) $data['purchase_order_id'],
            description: $data['description'],
            quantity: (float) ($data['quantity'] ?? 0),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            lineTotal: (float) ($data['line_total'] ?? 0),
            quantityReceived: array_key_exists('quantity_received', $data) ? (float) $data['quantity_received'] : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchaseOrderId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
            'quantity_received' => $this->quantityReceived,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
