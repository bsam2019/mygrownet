<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\BranchId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Branch implements Arrayable
{
    private function __construct(
        private BranchId $id, private CompanyId $companyId, private string $name, private ?string $code,
        private ?string $phone, private ?string $email, private ?string $address, private ?string $city,
        private ?string $country, private bool $isHeadOffice, private bool $isActive,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId, string $name, ?string $code = null, ?string $phone = null, ?string $email = null,
        ?string $address = null, ?string $city = null, ?string $country = null, bool $isHeadOffice = false, bool $isActive = true
    ): self {
        return new self(BranchId::generate(), $companyId, $name, $code, $phone, $email, $address, $city, $country, $isHeadOffice, $isActive, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(
        BranchId $id, CompanyId $companyId, string $name, ?string $code, ?string $phone, ?string $email,
        ?string $address, ?string $city, ?string $country, bool $isHeadOffice, bool $isActive,
        DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt
    ): self {
        return new self($id, $companyId, $name, $code, $phone, $email, $address, $city, $country, $isHeadOffice, $isActive, $createdAt, $updatedAt);
    }

    public function update(string $name, ?string $code, ?string $phone, ?string $email, ?string $address, ?string $city, ?string $country, bool $isHeadOffice, bool $isActive): void
    {
        $this->name = $name; $this->code = $code; $this->phone = $phone; $this->email = $email;
        $this->address = $address; $this->city = $city; $this->country = $country;
        $this->isHeadOffice = $isHeadOffice; $this->isActive = $isActive;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): BranchId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function isHeadOffice(): bool { return $this->isHeadOffice; }
    public function isActive(): bool { return $this->isActive; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name, 'code' => $this->code, 'phone' => $this->phone,
            'email' => $this->email, 'address' => $this->address, 'city' => $this->city,
            'country' => $this->country, 'is_head_office' => $this->isHeadOffice,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
