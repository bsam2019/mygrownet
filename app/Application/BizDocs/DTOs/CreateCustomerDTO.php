<?php

namespace App\Application\BizDocs\DTOs;

class CreateCustomerDTO
{
    public function __construct(
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $tpin = null,
        public readonly ?string $notes = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            businessId: $data['business_id'],
            name: $data['name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            tpin: $data['tpin'] ?? null,
            notes: $data['notes'] ?? null
        );
    }
}
