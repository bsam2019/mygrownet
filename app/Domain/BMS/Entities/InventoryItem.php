<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class InventoryItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly ?string $sku,
        public readonly ?string $description,
        public readonly ?string $unit,
        public readonly ?float $unitPrice,
        public readonly float $quantityOnHand,
        public readonly ?float $reorderLevel,
        public readonly ?int $categoryId,
        public readonly ?int $materialId,
        public readonly ?string $location,
        public readonly ?string $status,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            name: $data['name'],
            sku: $data['sku'] ?? null,
            description: $data['description'] ?? null,
            unit: $data['unit'] ?? null,
            unitPrice: array_key_exists('unit_price', $data) ? (float) $data['unit_price'] : null,
            quantityOnHand: (float) ($data['quantity_on_hand'] ?? 0),
            reorderLevel: array_key_exists('reorder_level', $data) ? (float) $data['reorder_level'] : null,
            categoryId: $data['category_id'] ?? null,
            materialId: $data['material_id'] ?? null,
            location: $data['location'] ?? null,
            status: $data['status'] ?? 'active',
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
            'sku' => $this->sku,
            'description' => $this->description,
            'unit' => $this->unit,
            'unit_price' => $this->unitPrice,
            'quantity_on_hand' => $this->quantityOnHand,
            'reorder_level' => $this->reorderLevel,
            'category_id' => $this->categoryId,
            'material_id' => $this->materialId,
            'location' => $this->location,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
