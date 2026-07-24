<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Customer
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $customerNumber,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $secondaryPhone,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $taxNumber,
        public readonly ?float $creditLimit,
        public readonly float $outstandingBalance,
        public readonly float $creditBalance,
        public readonly string $status,
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
            customerNumber: $data['customer_number'] ?? '',
            name: $data['name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            secondaryPhone: $data['secondary_phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            creditLimit: array_key_exists('credit_limit', $data) ? (float) $data['credit_limit'] : null,
            outstandingBalance: (float) ($data['outstanding_balance'] ?? 0),
            creditBalance: (float) ($data['credit_balance'] ?? 0),
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            companyId: $data['company_id'],
            customerNumber: $data['customer_number'] ?? '',
            name: $data['name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            secondaryPhone: $data['secondary_phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? 'Zambia',
            taxNumber: $data['tax_number'] ?? null,
            creditLimit: $data['credit_limit'] ?? null,
            outstandingBalance: 0,
            creditBalance: 0,
            status: 'active',
            notes: $data['notes'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function hasOutstandingBalance(): bool
    {
        return $this->outstandingBalance > 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'customer_number' => $this->customerNumber,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'secondary_phone' => $this->secondaryPhone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'tax_number' => $this->taxNumber,
            'credit_limit' => $this->creditLimit,
            'outstanding_balance' => $this->outstandingBalance,
            'credit_balance' => $this->creditBalance,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
