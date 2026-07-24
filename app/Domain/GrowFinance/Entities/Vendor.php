<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class Vendor
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $taxNumber,
        public readonly ?string $paymentTerms,
        public readonly float $outstandingBalance,
        public readonly bool $isActive,
        public readonly ?string $notes,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            paymentTerms: $data['payment_terms'] ?? null,
            outstandingBalance: (float) ($data['outstanding_balance'] ?? 0.0),
            isActive: (bool) ($data['is_active'] ?? true),
            notes: $data['notes'] ?? null,
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
            'payment_terms' => $this->paymentTerms,
            'outstanding_balance' => $this->outstandingBalance,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
