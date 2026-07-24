<?php

namespace App\Domain\BizBoost\Entities;

class OrderItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $orderId,
        public readonly ?int $productId,
        public readonly string $productName,
        public readonly float $unitPrice,
        public readonly int $quantity,
        public readonly float $subtotal,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            orderId: (int) $data['order_id'],
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            productName: $data['product_name'],
            unitPrice: (float) ($data['unit_price'] ?? 0),
            quantity: (int) ($data['quantity'] ?? 1),
            subtotal: (float) ($data['subtotal'] ?? 0),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'unit_price' => $this->unitPrice,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}