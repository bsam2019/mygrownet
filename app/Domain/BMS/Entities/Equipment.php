<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Equipment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly ?string $equipmentType,
        public readonly ?string $make,
        public readonly ?string $model,
        public readonly ?string $serialNumber,
        public readonly ?string $registrationNumber,
        public readonly string $status,
        public readonly ?string $location,
        public readonly ?DateTimeImmutable $purchaseDate,
        public readonly ?float $purchasePrice,
        public readonly ?float $currentValue,
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
            equipmentType: $data['equipment_type'] ?? null,
            make: $data['make'] ?? null,
            model: $data['model'] ?? null,
            serialNumber: $data['serial_number'] ?? null,
            registrationNumber: $data['registration_number'] ?? null,
            status: $data['status'] ?? 'active',
            location: $data['location'] ?? null,
            purchaseDate: isset($data['purchase_date']) ? new DateTimeImmutable($data['purchase_date']) : null,
            purchasePrice: array_key_exists('purchase_price', $data) ? (float) $data['purchase_price'] : null,
            currentValue: array_key_exists('current_value', $data) ? (float) $data['current_value'] : null,
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
            'equipment_type' => $this->equipmentType,
            'make' => $this->make,
            'model' => $this->model,
            'serial_number' => $this->serialNumber,
            'registration_number' => $this->registrationNumber,
            'status' => $this->status,
            'location' => $this->location,
            'purchase_date' => $this->purchaseDate?->format('Y-m-d'),
            'purchase_price' => $this->purchasePrice,
            'current_value' => $this->currentValue,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
