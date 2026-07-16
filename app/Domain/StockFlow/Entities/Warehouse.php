<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Warehouse implements Arrayable
{
    private function __construct(
        private WarehouseId $id,
        private CompanyId $companyId,
        private string $name,
        private ?string $code,
        private ?string $address,
        private ?string $city,
        private ?string $country,
        private ?string $contactPerson,
        private ?string $phone,
        private bool $isDefault,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId, string $name, ?string $code = null, ?string $address = null,
        ?string $city = null, ?string $country = null, ?string $contactPerson = null, ?string $phone = null, bool $isDefault = false
    ): self {
        return new self(WarehouseId::generate(), $companyId, $name, $code, $address, $city, $country, $contactPerson, $phone, $isDefault, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(WarehouseId $id, CompanyId $companyId, string $name, ?string $code, ?string $address, ?string $city, ?string $country, ?string $contactPerson, ?string $phone, bool $isDefault, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $name, $code, $address, $city, $country, $contactPerson, $phone, $isDefault, $createdAt, $updatedAt);
    }

    public function update(string $name, ?string $code, ?string $address, ?string $city, ?string $country, ?string $contactPerson, ?string $phone, bool $isDefault): void
    {
        $this->name = $name;
        $this->code = $code;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->contactPerson = $contactPerson;
        $this->phone = $phone;
        $this->isDefault = $isDefault;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): WarehouseId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function isDefault(): bool { return $this->isDefault; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'contact_person' => $this->contactPerson,
            'phone' => $this->phone,
            'is_default' => $this->isDefault,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
