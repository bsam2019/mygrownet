<?php

namespace App\Domain\Investment\Entities;

class InvestmentTier
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly float $minimumInvestment,
        public readonly ?float $maximumInvestment,
        public readonly ?array $benefits,
        public readonly ?string $description,
        public readonly int $sortOrder,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            minimumInvestment: (float) ($data['minimum_investment'] ?? 0),
            maximumInvestment: isset($data['maximum_investment']) ? (float) $data['maximum_investment'] : null,
            benefits: isset($data['benefits']) ? (array) $data['benefits'] : null,
            description: $data['description'] ?? null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'minimum_investment' => $this->minimumInvestment,
            'maximum_investment' => $this->maximumInvestment,
            'benefits' => $this->benefits,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
