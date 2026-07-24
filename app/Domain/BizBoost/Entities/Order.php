<?php

namespace App\Domain\BizBoost\Entities;

class Order
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $orderNumber,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly ?string $customerEmail,
        public readonly ?string $deliveryAddress,
        public readonly ?string $notes,
        public readonly float $subtotal,
        public readonly float $deliveryFee,
        public readonly float $total,
        public readonly ?string $currency,
        public readonly ?string $paymentMethod,
        public readonly string $paymentStatus,
        public readonly string $orderStatus,
        public readonly ?string $source,
        public readonly ?string $paymentReference,
        public readonly ?string $paidAt,
        public readonly ?string $deliveredAt,
        public readonly ?array $meta,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            orderNumber: $data['order_number'],
            customerName: $data['customer_name'],
            customerPhone: $data['customer_phone'],
            customerEmail: $data['customer_email'] ?? null,
            deliveryAddress: $data['delivery_address'] ?? null,
            notes: $data['notes'] ?? null,
            subtotal: (float) ($data['subtotal'] ?? 0),
            deliveryFee: (float) ($data['delivery_fee'] ?? 0),
            total: (float) ($data['total'] ?? 0),
            currency: $data['currency'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            paymentStatus: $data['payment_status'] ?? 'pending',
            orderStatus: $data['order_status'] ?? 'pending',
            source: $data['source'] ?? null,
            paymentReference: $data['payment_reference'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            deliveredAt: $data['delivered_at'] ?? null,
            meta: $data['meta'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'delivery_address' => $this->deliveryAddress,
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->deliveryFee,
            'total' => $this->total,
            'currency' => $this->currency,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'order_status' => $this->orderStatus,
            'source' => $this->source,
            'payment_reference' => $this->paymentReference,
            'paid_at' => $this->paidAt,
            'delivered_at' => $this->deliveredAt,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}