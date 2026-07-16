<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\CustomerId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Customer implements Arrayable
{
    private function __construct(
        private CustomerId $id,
        private CompanyId $companyId,
        private string $name,
        private ?string $phone,
        private ?string $email,
        private ?string $address,
        private ?string $city,
        private ?string $country,
        private ?Money $creditLimit,
        private ?string $paymentTerms,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId,
        string $name,
        ?string $phone = null,
        ?string $email = null,
        ?string $address = null,
        ?string $city = null,
        ?string $country = null,
        ?Money $creditLimit = null,
        ?string $paymentTerms = null,
        ?string $notes = null,
    ): self {
        return new self(
            id: CustomerId::generate(),
            companyId: $companyId,
            name: $name,
            phone: $phone,
            email: $email,
            address: $address,
            city: $city,
            country: $country,
            creditLimit: $creditLimit,
            paymentTerms: $paymentTerms,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        CustomerId $id,
        CompanyId $companyId,
        string $name,
        ?string $phone,
        ?string $email,
        ?string $address,
        ?string $city,
        ?string $country,
        ?Money $creditLimit,
        ?string $paymentTerms,
        ?string $notes,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $name, $phone, $email, $address, $city, $country, $creditLimit, $paymentTerms, $notes, $createdAt, $updatedAt);
    }

    public function updateDetails(
        string $name,
        ?string $phone,
        ?string $email,
        ?string $address,
        ?string $city,
        ?string $country,
        ?Money $creditLimit,
        ?string $paymentTerms,
        ?string $notes,
    ): void {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->creditLimit = $creditLimit;
        $this->paymentTerms = $paymentTerms;
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CustomerId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getPhone(): ?string { return $this->phone; }
    public function getEmail(): ?string { return $this->email; }
    public function getAddress(): ?string { return $this->address; }
    public function getCity(): ?string { return $this->city; }
    public function getCountry(): ?string { return $this->country; }
    public function getCreditLimit(): ?Money { return $this->creditLimit; }
    public function getPaymentTerms(): ?string { return $this->paymentTerms; }
    public function getNotes(): ?string { return $this->notes; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'credit_limit' => $this->creditLimit?->toFloat(),
            'payment_terms' => $this->paymentTerms,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
