<?php

namespace App\Domain\BizBoost\Entities;

class Sale
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $customerId,
        public readonly ?int $productId,
        public readonly ?string $productName,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $totalAmount,
        public readonly ?string $currency,
        public readonly string $saleDate,
        public readonly ?string $paymentMethod,
        public readonly ?string $source,
        public readonly ?int $linkedPostId,
        public readonly ?string $notes,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            productName: $data['product_name'] ?? null,
            quantity: (int) ($data['quantity'] ?? 1),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            currency: $data['currency'] ?? null,
            saleDate: $data['sale_date'],
            paymentMethod: $data['payment_method'] ?? null,
            source: $data['source'] ?? null,
            linkedPostId: isset($data['linked_post_id']) ? (int) $data['linked_post_id'] : null,
            notes: $data['notes'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            businessId: (int) $data['business_id'],
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            productId: isset($data['product_id']) ? (int) $data['product_id'] : null,
            productName: $data['product_name'] ?? null,
            quantity: (int) ($data['quantity'] ?? 1),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            currency: $data['currency'] ?? null,
            saleDate: $data['sale_date'],
            paymentMethod: $data['payment_method'] ?? null,
            source: $data['source'] ?? 'manual',
            linkedPostId: isset($data['linked_post_id']) ? (int) $data['linked_post_id'] : null,
            notes: $data['notes'] ?? null,
            createdAt: null,
            updatedAt: null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'customer_id' => $this->customerId,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'sale_date' => $this->saleDate,
            'payment_method' => $this->paymentMethod,
            'source' => $this->source,
            'linked_post_id' => $this->linkedPostId,
            'notes' => $this->notes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}