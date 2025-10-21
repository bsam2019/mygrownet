<?php

namespace App\Domain\ProfitSharing\Entities;

use App\Domain\ProfitSharing\ValueObjects\Quarter;
use App\Domain\ProfitSharing\ValueObjects\ProfitAmount;
use DateTimeImmutable;

class QuarterlyProfitShare
{
    private function __construct(
        private ?int $id,
        private Quarter $quarter,
        private ProfitAmount $totalProjectProfit,
        private int $totalActiveMembers,
        private ?float $totalBpPool,
        private string $distributionMethod,
        private string $status,
        private ?string $notes,
        private int $createdBy,
        private ?int $approvedBy,
        private ?DateTimeImmutable $approvedAt,
        private ?DateTimeImmutable $distributedAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        Quarter $quarter,
        ProfitAmount $totalProjectProfit,
        int $totalActiveMembers,
        ?float $totalBpPool,
        string $distributionMethod,
        int $createdBy,
        ?string $notes = null
    ): self {
        return new self(
            id: null,
            quarter: $quarter,
            totalProjectProfit: $totalProjectProfit,
            totalActiveMembers: $totalActiveMembers,
            totalBpPool: $totalBpPool,
            distributionMethod: $distributionMethod,
            status: 'draft',
            notes: $notes,
            createdBy: $createdBy,
            approvedBy: null,
            approvedAt: null,
            distributedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public function approve(int $approvedBy): void
    {
        if ($this->status !== 'calculated') {
            throw new \DomainException('Can only approve calculated profit shares');
        }

        $this->status = 'approved';
        $this->approvedBy = $approvedBy;
        $this->approvedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDistributed(): void
    {
        if ($this->status !== 'approved') {
            throw new \DomainException('Can only distribute approved profit shares');
        }

        $this->status = 'distributed';
        $this->distributedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsCalculated(): void
    {
        if ($this->status !== 'draft') {
            throw new \DomainException('Can only calculate draft profit shares');
        }

        $this->status = 'calculated';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function memberShareAmount(): ProfitAmount
    {
        return $this->totalProjectProfit->memberShare();
    }

    public function companyRetained(): ProfitAmount
    {
        return $this->totalProjectProfit->companyRetained();
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function quarter(): Quarter { return $this->quarter; }
    public function totalProjectProfit(): ProfitAmount { return $this->totalProjectProfit; }
    public function totalActiveMembers(): int { return $this->totalActiveMembers; }
    public function totalBpPool(): ?float { return $this->totalBpPool; }
    public function distributionMethod(): string { return $this->distributionMethod; }
    public function status(): string { return $this->status; }
    public function notes(): ?string { return $this->notes; }
    public function createdBy(): int { return $this->createdBy; }
    public function approvedBy(): ?int { return $this->approvedBy; }
    public function approvedAt(): ?DateTimeImmutable { return $this->approvedAt; }
    public function distributedAt(): ?DateTimeImmutable { return $this->distributedAt; }
}
