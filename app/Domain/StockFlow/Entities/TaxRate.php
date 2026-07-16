<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\TaxRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class TaxRate implements Arrayable
{
    private function __construct(
        private TaxRateId $id,
        private CompanyId $companyId,
        private string $name,
        private float $rate,
        private string $type,
        private bool $isDefault,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $name, float $rate, string $type = 'inclusive', bool $isDefault = false): self
    {
        return new self(TaxRateId::generate(), $companyId, $name, $rate, $type, $isDefault, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(TaxRateId $id, CompanyId $companyId, string $name, float $rate, string $type, bool $isDefault, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $name, $rate, $type, $isDefault, $createdAt, $updatedAt);
    }

    public function update(string $name, float $rate, string $type, bool $isDefault): void
    {
        $this->name = $name;
        $this->rate = $rate;
        $this->type = $type;
        $this->isDefault = $isDefault;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): TaxRateId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getName(): string { return $this->name; }
    public function getRate(): float { return $this->rate; }
    public function getType(): string { return $this->type; }
    public function isDefault(): bool { return $this->isDefault; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type,
            'is_default' => $this->isDefault,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
