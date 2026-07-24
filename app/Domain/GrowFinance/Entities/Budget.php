<?php

namespace App\Domain\GrowFinance\Entities;

class Budget
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $category = null,
        public readonly ?int $accountId = null,
        public readonly ?string $period = null,
        public readonly ?\DateTimeImmutable $startDate = null,
        public readonly ?\DateTimeImmutable $endDate = null,
        public readonly float $budgetedAmount = 0.0,
        public readonly float $spentAmount = 0.0,
        public readonly bool $isActive = true,
        public readonly bool $rolloverUnused = false,
        public readonly ?float $alertThreshold = null,
        public readonly ?string $notes = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function getRemainingAmount(): float
    {
        return max(0, $this->budgetedAmount - $this->spentAmount);
    }

    public function getSpentPercentage(): float
    {
        if ($this->budgetedAmount <= 0) {
            return 0.0;
        }

        return min(100, ($this->spentAmount / $this->budgetedAmount) * 100);
    }

    public function getStatus(): string
    {
        if ($this->isOverBudget()) {
            return 'over_budget';
        }

        if ($this->isNearLimit()) {
            return 'near_limit';
        }

        return 'on_track';
    }

    public function isOverBudget(): bool
    {
        return $this->spentAmount > $this->budgetedAmount;
    }

    public function isNearLimit(): bool
    {
        if ($this->budgetedAmount <= 0 || $this->alertThreshold === null || $this->alertThreshold <= 0) {
            return false;
        }

        return $this->spentAmount >= $this->budgetedAmount * ($this->alertThreshold / 100);
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            category: $data['category'] ?? null,
            accountId: isset($data['account_id']) ? (int) $data['account_id'] : null,
            period: $data['period'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            budgetedAmount: (float) ($data['budgeted_amount'] ?? 0.0),
            spentAmount: (float) ($data['spent_amount'] ?? 0.0),
            isActive: (bool) ($data['is_active'] ?? true),
            rolloverUnused: (bool) ($data['rollover_unused'] ?? false),
            alertThreshold: array_key_exists('alert_threshold', $data) ? (float) $data['alert_threshold'] : null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'category' => $this->category,
            'account_id' => $this->accountId,
            'period' => $this->period,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
            'budgeted_amount' => $this->budgetedAmount,
            'spent_amount' => $this->spentAmount,
            'is_active' => $this->isActive,
            'rollover_unused' => $this->rolloverUnused,
            'alert_threshold' => $this->alertThreshold,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}