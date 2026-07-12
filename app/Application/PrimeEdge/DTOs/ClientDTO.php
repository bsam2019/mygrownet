<?php

namespace App\Application\PrimeEdge\DTOs;

use App\Domain\PrimeEdge\Entities\Client;

class ClientDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $businessType,
        public readonly ?string $companyName,
        public readonly string $status,
        public readonly ?string $lastLoginAt,
        public readonly string $createdAt,
    ) {}

    public static function fromEntity(Client $client): self
    {
        return new self(
            id: $client->id()->toString(),
            name: $client->name()->toString(),
            email: $client->email()->toString(),
            phone: $client->phone()?->toString(),
            businessType: $client->businessType()?->toString(),
            companyName: $client->companyName(),
            status: $client->status()->value,
            lastLoginAt: $client->lastLoginAt()?->format('Y-m-d H:i:s'),
            createdAt: $client->createdAt()->format('Y-m-d H:i:s'),
        );
    }
}
