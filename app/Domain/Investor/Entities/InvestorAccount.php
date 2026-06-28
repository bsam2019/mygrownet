<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\InvestorStatus;
use DateTimeImmutable;

/**
 * Investor Account Entity
 * 
 * Represents an investor's account and investment details
 */
class InvestorAccount
{
    private function __construct(
        private readonly int $id,
        private ?int $userId,
        private string $name,
        private string $email,
        private float $investmentAmount,
        private DateTimeImmutable $investmentDate,
        private ?int $investmentRoundId,
        private InvestorStatus $status,
        private float $equityPercentage,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        ?int $userId,
        string $name,
        string $email,
        float $investmentAmount,
        DateTimeImmutable $investmentDate,
        ?int $investmentRoundId,
        float $equityPercentage
    ): self {
        $now = new DateTimeImmutable();
        
        // Determine initial status based on investment amount
        $status = $investmentAmount > 0 ? InvestorStatus::ciu() : InvestorStatus::prospective();
        
        return new self(
            id: 0,
            userId: $userId,
            name: $name,
            email: $email,
            investmentAmount: $investmentAmount,
            investmentDate: $investmentDate,
            investmentRoundId: $investmentRoundId,
            status: $status,
            equityPercentage: $equityPercentage,
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function fromPersistence(
        int $id,
        ?int $userId,
        string $name,
        string $email,
        float $investmentAmount,
        DateTimeImmutable $investmentDate,
        ?int $investmentRoundId,
        InvestorStatus $status,
        float $equityPercentage,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            name: $name,
            email: $email,
            investmentAmount: $investmentAmount,
            investmentDate: $investmentDate,
            investmentRoundId: $investmentRoundId,
            status: $status,
            equityPercentage: $equityPercentage,
            createdAt: $createdAt,
            updatedAt: $updatedAt
        );
    }

    // Business logic
    public function convertToShareholder(): void
    {
        $this->status = InvestorStatus::shareholder();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function exit(): void
    {
        $this->status = InvestorStatus::exited();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isShareholder(): bool
    {
        return $this->status->equals(InvestorStatus::shareholder());
    }

    public function isCIU(): bool
    {
        return $this->status->equals(InvestorStatus::ciu());
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getInvestmentAmount(): float
    {
        return $this->investmentAmount;
    }

    public function getInvestmentDate(): DateTimeImmutable
    {
        return $this->investmentDate;
    }

    public function getInvestmentRoundId(): ?int
    {
        return $this->investmentRoundId;
    }

    public function isProspective(): bool
    {
        return $this->status->equals(InvestorStatus::prospective());
    }

    public function hasInvested(): bool
    {
        return $this->investmentAmount > 0;
    }

    /**
     * Record an investment for a prospective investor
     */
    public function recordInvestment(
        float $amount,
        int $investmentRoundId,
        float $equityPercentage = 0
    ): void {
        $this->investmentAmount = $amount;
        $this->investmentRoundId = $investmentRoundId;
        $this->investmentDate = new DateTimeImmutable();
        $this->equityPercentage = $equityPercentage;
        $this->status = InvestorStatus::ciu();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStatus(): InvestorStatus
    {
        return $this->status;
    }

    public function getEquityPercentage(): float
    {
        return $this->equityPercentage;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
