<?php

namespace App\Domain\Module\Entities;

use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\SubscriptionId;
use App\Domain\Module\ValueObjects\SubscriptionStatus;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use App\Domain\Module\ValueObjects\Money;

/**
 * Module Subscription Entity
 * 
 * Represents a user's subscription to a specific module.
 */
class ModuleSubscription
{
    public function __construct(
        private ?SubscriptionId $id,
        private int $userId,
        private string $moduleId,
        private string $subscriptionTier,
        private string $status,
        private \DateTimeImmutable $startedAt,
        private ?\DateTimeImmutable $trialEndsAt,
        private ?\DateTimeImmutable $expiresAt,
        private ?\DateTimeImmutable $cancelledAt,
        private bool $autoRenew,
        private string $billingCycle,
        private Money $amount,
        private ?int $userLimit,
        private ?int $storageLimitMb
    ) {}

    public static function create(
        int $userId,
        string $moduleId,
        string $subscriptionTier,
        Money $amount,
        string $billingCycle = 'monthly'
    ): self {
        return new self(
            id: null,
            userId: $userId,
            moduleId: $moduleId,
            subscriptionTier: $subscriptionTier,
            status: 'active',
            startedAt: new \DateTimeImmutable(),
            trialEndsAt: null,
            expiresAt: self::calculateExpiration($billingCycle),
            cancelledAt: null,
            autoRenew: true,
            billingCycle: $billingCycle,
            amount: $amount,
            userLimit: null,
            storageLimitMb: null
        );
    }

    public static function createTrial(
        int $userId,
        string $moduleId,
        string $subscriptionTier,
        int $trialDays = 14
    ): self {
        $trialEndsAt = (new \DateTimeImmutable())->modify("+{$trialDays} days");
        
        return new self(
            id: null,
            userId: $userId,
            moduleId: $moduleId,
            subscriptionTier: $subscriptionTier,
            status: 'trial',
            startedAt: new \DateTimeImmutable(),
            trialEndsAt: $trialEndsAt,
            expiresAt: $trialEndsAt,
            cancelledAt: null,
            autoRenew: true,
            billingCycle: 'monthly',
            amount: Money::zero(),
            userLimit: null,
            storageLimitMb: null
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->cancelledAt = new \DateTimeImmutable();
        $this->autoRenew = false;
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
    }

    public function reactivate(): void
    {
        if ($this->status === 'cancelled') {
            throw new \DomainException('Cannot reactivate a cancelled subscription');
        }

        $this->status = 'active';
    }

    public function convertFromTrial(Money $amount, string $billingCycle): void
    {
        if (!$this->isTrial()) {
            throw new \DomainException('Can only convert trial subscriptions');
        }

        $this->status = 'active';
        $this->amount = $amount;
        $this->billingCycle = $billingCycle;
        $this->expiresAt = self::calculateExpiration($billingCycle);
    }

    public function renew(): void
    {
        if (!$this->autoRenew) {
            throw new \DomainException('Auto-renew is disabled');
        }

        $this->expiresAt = self::calculateExpiration($this->billingCycle, $this->expiresAt);
    }

    public function upgradeTier(string $newTier, Money $newAmount): void
    {
        $this->subscriptionTier = $newTier;
        $this->amount = $newAmount;
    }
    
    public function setId(SubscriptionId $id): void
    {
        $this->id = $id;
    }

    public function setLimits(?int $userLimit, ?int $storageLimitMb): void
    {
        $this->userLimit = $userLimit;
        $this->storageLimitMb = $storageLimitMb;
    }

    private static function calculateExpiration(
        string $billingCycle,
        ?\DateTimeImmutable $from = null
    ): \DateTimeImmutable {
        $from = $from ?? new \DateTimeImmutable();
        
        return match($billingCycle) {
            'monthly' => $from->modify('+1 month'),
            'annual' => $from->modify('+1 year'),
            default => throw new \InvalidArgumentException("Invalid billing cycle: {$billingCycle}")
        };
    }

    // Getters
    public function getId(): ?SubscriptionId { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getModuleId(): string { return $this->moduleId; }
    public function getSubscriptionTier(): string { return $this->subscriptionTier; }
    public function getStatus(): string { return $this->status; }
    public function getAmount(): Money { return $this->amount; }
    public function getBillingCycle(): string { return $this->billingCycle; }
    public function isAutoRenew(): bool { return $this->autoRenew; }
    public function getStartedAt(): \DateTimeImmutable { return $this->startedAt; }
    public function getTrialEndsAt(): ?\DateTimeImmutable { return $this->trialEndsAt; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function getCancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function getUserLimit(): ?int { return $this->userLimit; }
    public function getStorageLimitMb(): ?int { return $this->storageLimitMb; }
}
