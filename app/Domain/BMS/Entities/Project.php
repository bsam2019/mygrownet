<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Project
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?DateTimeImmutable $startDate,
        public readonly ?DateTimeImmutable $endDate,
        public readonly ?float $budget,
        public readonly ?float $actualCost,
        public readonly string $status,
        public readonly ?int $managerId,
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
            description: $data['description'] ?? null,
            startDate: isset($data['start_date']) ? new DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new DateTimeImmutable($data['end_date']) : null,
            budget: array_key_exists('budget', $data) ? (float) $data['budget'] : null,
            actualCost: array_key_exists('actual_cost', $data) ? (float) $data['actual_cost'] : null,
            status: $data['status'] ?? 'planning',
            managerId: $data['manager_id'] ?? null,
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
            'description' => $this->description,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'budget' => $this->budget,
            'actual_cost' => $this->actualCost,
            'status' => $this->status,
            'manager_id' => $this->managerId,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
