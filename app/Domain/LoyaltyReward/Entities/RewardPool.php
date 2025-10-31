<?php

namespace App\Domain\LoyaltyReward\Entities;

use App\Domain\LoyaltyReward\ValueObjects\LoyaltyAmount;
use DateTimeImmutable;

class RewardPool
{
    private const MINIMUM_RESERVE_PERCENTAGE = 30;

    private function __construct(
        private int $id,
        private LoyaltyAmount $totalBalance,
        private LoyaltyAmount $availableBalance,
        private LoyaltyAmount $reservedBalance,
        private DateTimeImmutable $lastUpdated
    ) {}

    public static function create(): self
    {
        return new self(
            id: 0,
            totalBalance: LoyaltyAmount::zero(),
            availableBalance: LoyaltyAmount::zero(),
            reservedBalance: LoyaltyAmount::zero(),
            lastUpdated: new DateTimeImmutable()
        );
    }

    public function addFunds(LoyaltyAmount $amount, string $source): void
    {
        $this->totalBalance = $this->totalBalance->add($amount);
        
        // Calculate reserve requirement
        $reserveAmount = $amount->calculatePercentage(self::MINIMUM_RESERVE_PERCENTAGE);
        $this->reservedBalance = $this->reservedBalance->add($reserveAmount);
        
        // Add remaining to available balance
        $availableAmount = $amount->subtract($reserveAmount);
        $this->availableBalance = $this->availableBalance->add($availableAmount);
        
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function allocateRewards(LoyaltyAmount $amount): void
    {
        if ($amount->isGreaterThan($this->availableBalance)) {
            throw new \DomainException('Insufficient available balance in reward pool');
        }

        $this->availableBalance = $this->availableBalance->subtract($amount);
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function calculateProportionalAmount(
        LoyaltyAmount $requestedAmount,
        LoyaltyAmount $totalRequested
    ): LoyaltyAmount {
        if ($totalRequested->isLessThanOrEqual($this->availableBalance)) {
            return $requestedAmount;
        }

        // Calculate proportional share
        $proportion = $this->availableBalance->toFloat() / $totalRequested->toFloat();
        return LoyaltyAmount::fromKwacha((int)($requestedAmount->toKwacha() * $proportion));
    }

    public function hasMinimumReserve(): bool
    {
        $requiredReserve = $this->totalBalance->calculatePercentage(self::MINIMUM_RESERVE_PERCENTAGE);
        return $this->reservedBalance->isGreaterThanOrEqual($requiredReserve);
    }

    public function canAllocate(LoyaltyAmount $amount): bool
    {
        return $amount->isLessThanOrEqual($this->availableBalance);
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getTotalBalance(): LoyaltyAmount
    {
        return $this->totalBalance;
    }

    public function getAvailableBalance(): LoyaltyAmount
    {
        return $this->availableBalance;
    }

    public function getReservedBalance(): LoyaltyAmount
    {
        return $this->reservedBalance;
    }

    public function getLastUpdated(): DateTimeImmutable
    {
        return $this->lastUpdated;
    }
}
