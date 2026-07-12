<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\PartnerId;
use App\Domain\PrimeEdge\ValueObjects\PartnerType;
use DateTimeImmutable;

class ReferralPartner
{
    private function __construct(
        private readonly PartnerId $id,
        private string $name,
        private string $contactPerson,
        private string $email,
        private ?string $phone,
        private PartnerType $type,
        private string $specialization,
        private bool $active,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        PartnerId $id,
        string $name,
        string $contactPerson,
        string $email,
        PartnerType $type,
        string $specialization = '',
        ?string $phone = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            contactPerson: $contactPerson,
            email: $email,
            phone: $phone,
            type: $type,
            specialization: $specialization,
            active: true,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        PartnerId $id,
        string $name,
        string $contactPerson,
        string $email,
        ?string $phone,
        PartnerType $type,
        string $specialization,
        bool $active,
        ?string $notes,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            contactPerson: $contactPerson,
            email: $email,
            phone: $phone,
            type: $type,
            specialization: $specialization,
            active: $active,
            notes: $notes,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function update(string $name, string $contactPerson, string $email, ?string $phone, PartnerType $type, string $specialization, ?string $notes): void
    {
        $this->name = $name;
        $this->contactPerson = $contactPerson;
        $this->email = $email;
        $this->phone = $phone;
        $this->type = $type;
        $this->specialization = $specialization;
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): PartnerId { return $this->id; }
    public function name(): string { return $this->name; }
    public function contactPerson(): string { return $this->contactPerson; }
    public function email(): string { return $this->email; }
    public function phone(): ?string { return $this->phone; }
    public function type(): PartnerType { return $this->type; }
    public function specialization(): string { return $this->specialization; }
    public function isActive(): bool { return $this->active; }
    public function notes(): ?string { return $this->notes; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
