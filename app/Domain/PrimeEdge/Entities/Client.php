<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\ClientName;
use App\Domain\PrimeEdge\ValueObjects\ClientStatus;
use App\Domain\PrimeEdge\ValueObjects\Email;
use App\Domain\PrimeEdge\ValueObjects\PhoneNumber;
use App\Domain\PrimeEdge\ValueObjects\BusinessType;
use DateTimeImmutable;

class Client
{
    private function __construct(
        private readonly ClientId $id,
        private ClientName $name,
        private Email $email,
        private string $password,
        private ?PhoneNumber $phone,
        private ?BusinessType $businessType,
        private ?string $companyName,
        private ClientStatus $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $lastLoginAt,
    ) {}

    public static function create(
        ClientId $id,
        ClientName $name,
        Email $email,
        string $password,
        ?PhoneNumber $phone = null,
        ?BusinessType $businessType = null,
        ?string $companyName = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            password: $password,
            phone: $phone,
            businessType: $businessType,
            companyName: $companyName,
            status: ClientStatus::ACTIVE,
            createdAt: new DateTimeImmutable(),
            updatedAt: null,
            lastLoginAt: null,
        );
    }

    public static function reconstitute(
        ClientId $id,
        ClientName $name,
        Email $email,
        string $password,
        ?PhoneNumber $phone,
        ?BusinessType $businessType,
        ?string $companyName,
        ClientStatus $status,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $lastLoginAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            password: $password,
            phone: $phone,
            businessType: $businessType,
            companyName: $companyName,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            lastLoginAt: $lastLoginAt,
        );
    }

    public function updateProfile(ClientName $name, ?PhoneNumber $phone, ?BusinessType $businessType, ?string $companyName): void
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->businessType = $businessType;
        $this->companyName = $companyName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markLoggedIn(): void
    {
        $this->lastLoginAt = new DateTimeImmutable();
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        if (!$this->status->canTransitionTo(ClientStatus::INACTIVE)) {
            throw new \DomainException("Cannot deactivate client in status {$this->status->value}");
        }
        $this->status = ClientStatus::INACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reactivate(): void
    {
        if (!$this->status->canTransitionTo(ClientStatus::ACTIVE)) {
            throw new \DomainException("Cannot reactivate client in status {$this->status->value}");
        }
        $this->status = ClientStatus::ACTIVE;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): ClientId { return $this->id; }
    public function name(): ClientName { return $this->name; }
    public function email(): Email { return $this->email; }
    public function password(): string { return $this->password; }
    public function phone(): ?PhoneNumber { return $this->phone; }
    public function businessType(): ?BusinessType { return $this->businessType; }
    public function companyName(): ?string { return $this->companyName; }
    public function status(): ClientStatus { return $this->status; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
    public function lastLoginAt(): ?DateTimeImmutable { return $this->lastLoginAt; }
}
