<?php

namespace App\Domain\Reward\ValueObjects;

class MaintenanceStatus
{
    public function __construct(
        private bool $compliant,
        private int $monthsCompleted
    ) {
        if ($monthsCompleted < 0) {
            throw new \InvalidArgumentException('Months completed cannot be negative');
        }
    }

    public function isCompliant(): bool
    {
        return $this->compliant;
    }

    public function getMonthsCompleted(): int
    {
        return $this->monthsCompleted;
    }

    public function markCompliant(): self
    {
        return new self(true, $this->monthsCompleted + 1);
    }

    public function markNonCompliant(): self
    {
        return new self(false, 0); // Reset months completed on violation
    }

    public function hasCompletedMonths(int $requiredMonths): bool
    {
        return $this->compliant && $this->monthsCompleted >= $requiredMonths;
    }

    public function getCompliancePercentage(int $requiredMonths): float
    {
        if ($requiredMonths <= 0) {
            return 100.0;
        }

        return min(100.0, ($this->monthsCompleted / $requiredMonths) * 100);
    }

    public function equals(MaintenanceStatus $other): bool
    {
        return $this->compliant === $other->compliant && 
               $this->monthsCompleted === $other->monthsCompleted;
    }

    public function toArray(): array
    {
        return [
            'compliant' => $this->compliant,
            'months_completed' => $this->monthsCompleted
        ];
    }
}