<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;

class Supplier
{
    private function __construct(
        private SupplierId $id,
        private CompanyId $companyId,
        private string $name,
        private ?string $contactPerson,
        private ?string $phone,
        private ?string $email,
        private ?string $address,
        private ?string $paymentTerms,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $name, ?string $contactPerson = null, ?string $phone = null, ?string $email = null, ?string $address = null, ?string $paymentTerms = null): self
    {
        return new self(SupplierId::generate(), $companyId, $name, $contactPerson, $phone, $email, $address, $paymentTerms, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(SupplierId $id, CompanyId $companyId, string $name, ?string $contactPerson, ?string $phone, ?string $email, ?string $address, ?string $paymentTerms, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $name, $contactPerson, $phone, $email, $address, $paymentTerms, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): SupplierId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getContactPerson(): ?string { return $this->contactPerson; }
    public function getPhone(): ?string { return $this->phone; }
    public function getEmail(): ?string { return $this->email; }
    public function getAddress(): ?string { return $this->address; }
    public function getPaymentTerms(): ?string { return $this->paymentTerms; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'contact_person' => $this->contactPerson,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'payment_terms' => $this->paymentTerms,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
