<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use DateTimeImmutable;

class FixedAsset
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $category,
        public readonly DateTimeImmutable $purchaseDate,
        public readonly float $cost,
        public readonly float $residualValue,
        public readonly int $usefulLifeMonths,
        public readonly DepreciationMethod $depreciationMethod,
        public readonly ?float $depreciationRate,
        public readonly float $accumulatedDepreciation,
        public readonly AssetStatus $status,
        public readonly ?DateTimeImmutable $disposalDate = null,
        public readonly ?float $disposalProceeds = null,
        public readonly ?string $location = null,
        public readonly ?string $serialNumber = null,
        public readonly ?string $notes = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function getNetBookValue(): float
    {
        return max(0, $this->cost - $this->accumulatedDepreciation);
    }

    public function getDepreciableAmount(): float
    {
        return max(0, $this->cost - $this->residualValue);
    }

    public function getMonthlyStraightLineDepreciation(): float
    {
        if ($this->usefulLifeMonths <= 0) return 0;
        return $this->getDepreciableAmount() / $this->usefulLifeMonths;
    }

    public function isFullyDepreciated(): bool
    {
        return $this->accumulatedDepreciation >= $this->getDepreciableAmount();
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            category: $data['category'] ?? null,
            purchaseDate: new DateTimeImmutable($data['purchase_date']),
            cost: (float) ($data['cost'] ?? 0),
            residualValue: (float) ($data['residual_value'] ?? 0),
            usefulLifeMonths: (int) ($data['useful_life_months'] ?? 0),
            depreciationMethod: DepreciationMethod::from($data['depreciation_method'] ?? 'straight_line'),
            depreciationRate: isset($data['depreciation_rate']) ? (float) $data['depreciation_rate'] : null,
            accumulatedDepreciation: (float) ($data['accumulated_depreciation'] ?? 0),
            status: AssetStatus::from($data['status'] ?? 'active'),
            disposalDate: isset($data['disposal_date']) ? new DateTimeImmutable($data['disposal_date']) : null,
            disposalProceeds: isset($data['disposal_proceeds']) ? (float) $data['disposal_proceeds'] : null,
            location: $data['location'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'category' => $this->category,
            'purchase_date' => $this->purchaseDate->format('Y-m-d'),
            'cost' => $this->cost,
            'residual_value' => $this->residualValue,
            'useful_life_months' => $this->usefulLifeMonths,
            'depreciation_method' => $this->depreciationMethod->value,
            'depreciation_rate' => $this->depreciationRate,
            'accumulated_depreciation' => $this->accumulatedDepreciation,
            'net_book_value' => $this->getNetBookValue(),
            'status' => $this->status->value,
            'disposal_date' => $this->disposalDate?->format('Y-m-d'),
            'disposal_proceeds' => $this->disposalProceeds,
            'location' => $this->location,
            'serial_number' => $this->serialNumber,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
