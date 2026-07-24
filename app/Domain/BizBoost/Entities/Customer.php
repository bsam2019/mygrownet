<?php

namespace App\Domain\BizBoost\Entities;

class Customer
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $whatsapp,
        public readonly ?string $address,
        public readonly ?string $notes,
        public readonly ?string $source,
        public readonly ?string $birthday,
        public readonly float $totalSpent,
        public readonly int $totalOrders,
        public readonly ?string $lastPurchaseAt,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            whatsapp: $data['whatsapp'] ?? null,
            address: $data['address'] ?? null,
            notes: $data['notes'] ?? null,
            source: $data['source'] ?? null,
            birthday: $data['birthday'] ?? null,
            totalSpent: (float) ($data['total_spent'] ?? 0),
            totalOrders: (int) ($data['total_orders'] ?? 0),
            lastPurchaseAt: $data['last_purchase_at'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            whatsapp: $data['whatsapp'] ?? $data['phone'] ?? null,
            address: $data['address'] ?? null,
            notes: $data['notes'] ?? null,
            source: $data['source'] ?? null,
            birthday: $data['birthday'] ?? null,
            totalSpent: 0,
            totalOrders: 0,
            lastPurchaseAt: null,
            isActive: true,
            createdAt: null,
            updatedAt: null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'notes' => $this->notes,
            'source' => $this->source,
            'birthday' => $this->birthday,
            'total_spent' => $this->totalSpent,
            'total_orders' => $this->totalOrders,
            'last_purchase_at' => $this->lastPurchaseAt,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}