<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class Company
{
    private function __construct(
        private CompanyId $id,
        private string $name,
        private ?string $subdomain,
        private ?string $email,
        private ?string $phone,
        private ?string $address,
        private ?string $city,
        private ?string $country,
        private ?string $contactPerson,
        private string $currency,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $name,
        ?string $email = null,
        ?string $phone = null,
        ?string $address = null,
        ?string $city = null,
        ?string $country = null,
        ?string $contactPerson = null,
        string $currency = 'MWK',
        string $status = 'active',
        ?string $subdomain = null,
    ): self {
        return new self(
            id: CompanyId::generate(),
            name: $name,
            subdomain: $subdomain,
            email: $email,
            phone: $phone,
            address: $address,
            city: $city,
            country: $country,
            contactPerson: $contactPerson,
            currency: $currency,
            status: $status,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        CompanyId $id,
        string $name,
        ?string $subdomain,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $city,
        ?string $country,
        ?string $contactPerson,
        string $currency,
        string $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $name, $subdomain, $email, $phone, $address, $city, $country, $contactPerson, $currency, $status, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CompanyId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getAddress(): ?string { return $this->address; }
    public function getCity(): ?string { return $this->city; }
    public function getCountry(): ?string { return $this->country; }
    public function getContactPerson(): ?string { return $this->contactPerson; }
    public function getCurrency(): string { return $this->currency; }
    public function getStatus(): string { return $this->status; }
    public function getSubdomain(): ?string { return $this->subdomain; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'name' => $this->name,
            'subdomain' => $this->subdomain,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'contact_person' => $this->contactPerson,
            'currency' => $this->currency,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
