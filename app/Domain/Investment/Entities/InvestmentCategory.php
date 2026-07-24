<?php

namespace App\Domain\Investment\Entities;

class InvestmentCategory
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly float $minInvestment,
        public readonly ?float $maxInvestment,
        public readonly ?float $interestRate,
        public readonly ?float $expectedRoi,
        public readonly ?int $lockInPeriod,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            minInvestment: (float) ($data['min_investment'] ?? 0),
            maxInvestment: isset($data['max_investment']) ? (float) $data['max_investment'] : null,
            interestRate: isset($data['interest_rate']) ? (float) $data['interest_rate'] : null,
            expectedRoi: isset($data['expected_roi']) ? (float) $data['expected_roi'] : null,
            lockInPeriod: isset($data['lock_in_period']) ? (int) $data['lock_in_period'] : null,
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
            'slug' => $this->slug,
            'description' => $this->description,
            'min_investment' => $this->minInvestment,
            'max_investment' => $this->maxInvestment,
            'interest_rate' => $this->interestRate,
            'expected_roi' => $this->expectedRoi,
            'lock_in_period' => $this->lockInPeriod,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
