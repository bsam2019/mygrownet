<?php

namespace App\Domain\GrowFinance\Entities;

class Customer
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?string $taxNumber = null,
        public readonly ?float $creditLimit = null,
        public readonly float $outstandingBalance = 0.0,
        public readonly ?string $notes = null,
        public readonly bool $isActive = true,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            creditLimit: array_key_exists('credit_limit', $data) ? (float) $data['credit_limit'] : null,
            outstandingBalance: (float) ($data['outstanding_balance'] ?? 0.0),
            notes: $data['notes'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'tax_number' => $this->taxNumber,
            'credit_limit' => $this->creditLimit,
            'outstanding_balance' => $this->outstandingBalance,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}