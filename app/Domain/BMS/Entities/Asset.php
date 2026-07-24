<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Asset
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly ?string $assetType,
        public readonly ?string $serialNumber,
        public readonly ?float $purchasePrice,
        public readonly ?DateTimeImmutable $purchaseDate,
        public readonly ?float $currentValue,
        public readonly ?float $salvageValue,
        public readonly ?int $usefulLifeYears,
        public readonly ?string $status,
        public readonly ?string $location,
        public readonly ?string $notes,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            name: $data['name'],
            assetType: $data['asset_type'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            purchasePrice: array_key_exists('purchase_price', $data) ? (float) $data['purchase_price'] : null,
            purchaseDate: isset($data['purchase_date']) ? new DateTimeImmutable($data['purchase_date']) : null,
            currentValue: array_key_exists('current_value', $data) ? (float) $data['current_value'] : null,
            salvageValue: array_key_exists('salvage_value', $data) ? (float) $data['salvage_value'] : null,
            usefulLifeYears: $data['useful_life_years'] ?? null,
            status: $data['status'] ?? 'active',
            location: $data['location'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'asset_type' => $this->assetType,
            'serial_number' => $this->serialNumber,
            'purchase_price' => $this->purchasePrice,
            'purchase_date' => $this->purchaseDate?->format('Y-m-d'),
            'current_value' => $this->currentValue,
            'salvage_value' => $this->salvageValue,
            'useful_life_years' => $this->usefulLifeYears,
            'status' => $this->status,
            'location' => $this->location,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
