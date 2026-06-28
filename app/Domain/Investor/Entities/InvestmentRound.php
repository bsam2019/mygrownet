<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\InvestmentRoundStatus;
use DateTimeImmutable;

/**
 * Investment Round Entity
 * 
 * Represents a fundraising round for the platform
 */
class InvestmentRound
{
    private function __construct(
        private readonly int $id,
        private string $name,
        private string $description,
        private float $goalAmount,
        private float $raisedAmount,
        private float $minimumInvestment,
        private float $valuation,
        private float $equityPercentage,
        private string $expectedRoi,
        private array $useOfFunds,
        private InvestmentRoundStatus $status,
        private ?DateTimeImmutable $startDate,
        private ?DateTimeImmutable $endDate,
        private bool $isFeatured,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $name,
        string $description,
        float $goalAmount,
        float $minimumInvestment,
        float $valuation,
        float $equityPercentage,
        string $expectedRoi,
        array $useOfFunds
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: 0,
            name: $name,
            description: $description,
            goalAmount: $goalAmount,
            raisedAmount: 0,
            minimumInvestment: $minimumInvestment,
            valuation: $valuation,
            equityPercentage: $equityPercentage,
            expectedRoi: $expectedRoi,
            useOfFunds: $useOfFunds,
            status: InvestmentRoundStatus::draft(),
            startDate: null,
            endDate: null,
            isFeatured: false,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        string $name,
        string $description,
        float $goalAmount,
        float $raisedAmount,
        float $minimumInvestment,
        float $valuation,
        float $equityPercentage,
        string $expectedRoi,
        array $useOfFunds,
        InvestmentRoundStatus $status,
        ?DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate,
        bool $isFeatured,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            name: $name,
            description: $description,
            goalAmount: $goalAmount,
            raisedAmount: $raisedAmount,
            minimumInvestment: $minimumInvestment,
            valuation: $valuation,
            equityPercentage: $equityPercentage,
            expectedRoi: $expectedRoi,
            useOfFunds: $useOfFunds,
            status: $status,
            startDate: $startDate,
            endDate: $endDate,
            isFeatured: $isFeatured,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // Business logic methods
    public function activate(?DateTimeImmutable $startDate = null, ?DateTimeImmutable $endDate = null): void
    {
        $this->status = InvestmentRoundStatus::active();
        $this->startDate = $startDate ?? new DateTimeImmutable();
        $this->endDate = $endDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function close(): void
    {
        $this->status = InvestmentRoundStatus::closed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status = InvestmentRoundStatus::completed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setAsFeatured(): void
    {
        $this->isFeatured = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeFeatured(): void
    {
        $this->isFeatured = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addInvestment(float $amount): void
    {
        $this->raisedAmount += $amount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getProgressPercentage(): float
    {
        if ($this->goalAmount === 0.0) {
            return 0.0;
        }
        
        return round(($this->raisedAmount / $this->goalAmount) * 100, 1);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->goalAmount - $this->raisedAmount);
    }

    public function isActive(): bool
    {
        return $this->status->equals(InvestmentRoundStatus::active());
    }

    public function isGoalReached(): bool
    {
        return $this->raisedAmount >= $this->goalAmount;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGoalAmount(): float
    {
        return $this->goalAmount;
    }

    public function getRaisedAmount(): float
    {
        return $this->raisedAmount;
    }

    public function getMinimumInvestment(): float
    {
        return $this->minimumInvestment;
    }

    public function getValuation(): float
    {
        return $this->valuation;
    }

    public function getEquityPercentage(): float
    {
        return $this->equityPercentage;
    }

    public function getExpectedRoi(): string
    {
        return $this->expectedRoi;
    }

    public function getUseOfFunds(): array
    {
        return $this->useOfFunds;
    }

    public function getStatus(): InvestmentRoundStatus
    {
        return $this->status;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Setters for updates
    public function updateDetails(
        string $name,
        string $description,
        float $goalAmount,
        float $minimumInvestment,
        float $valuation,
        float $equityPercentage,
        string $expectedRoi,
        array $useOfFunds
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->goalAmount = $goalAmount;
        $this->minimumInvestment = $minimumInvestment;
        $this->valuation = $valuation;
        $this->equityPercentage = $equityPercentage;
        $this->expectedRoi = $expectedRoi;
        $this->useOfFunds = $useOfFunds;
        $this->updatedAt = new DateTimeImmutable();
    }
}
