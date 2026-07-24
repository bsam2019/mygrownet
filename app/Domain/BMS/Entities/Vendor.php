<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Vendor
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly ?string $contactPerson,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $address,
        public readonly ?string $taxNumber,
        public readonly ?string $paymentTerms,
        public readonly string $status,
        public readonly ?string $notes,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            name: $data['name'],
            contactPerson: $data['contact_person'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            paymentTerms: $data['payment_terms'] ?? null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'contact_person' => $this->contactPerson,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'tax_number' => $this->taxNumber,
            'payment_terms' => $this->paymentTerms,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
