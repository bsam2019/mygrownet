<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Material
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $unit,
        public readonly ?float $unitPrice,
        public readonly ?float $wasteFactor,
        public readonly ?int $leadTimeDays,
        public readonly ?string $supplier,
        public readonly string $status,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            categoryId: (int) $data['category_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            unit: $data['unit'] ?? null,
            unitPrice: array_key_exists('unit_price', $data) ? (float) $data['unit_price'] : null,
            wasteFactor: array_key_exists('waste_factor', $data) ? (float) $data['waste_factor'] : null,
            leadTimeDays: $data['lead_time_days'] ?? null,
            supplier: $data['supplier'] ?? null,
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
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'unit_price' => $this->unitPrice,
            'waste_factor' => $this->wasteFactor,
            'lead_time_days' => $this->leadTimeDays,
            'supplier' => $this->supplier,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
