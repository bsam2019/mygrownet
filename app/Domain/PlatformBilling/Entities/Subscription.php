<?php

namespace App\Domain\PlatformBilling\Entities;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Suspended = 'suspended';
    case Pending = 'pending';
    case Trial = 'trial';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Active, self::Trial, self::Cancelled], true),
            self::Trial => in_array($target, [self::Active, self::Expired, self::Cancelled], true),
            self::Active => in_array($target, [self::Expired, self::Cancelled, self::Suspended], true),
            self::Suspended => in_array($target, [self::Active, self::Expired, self::Cancelled], true),
            self::Expired => in_array($target, [self::Active, self::Cancelled], true),
            self::Cancelled => false,
        };
    }
}

class Subscription
{
    private function __construct(
        private readonly ?int $id,
        private int $userId,
        private int $planId,
        private float $amount,
        private SubscriptionStatus $status,
        private ?\DateTimeImmutable $startDate,
        private ?\DateTimeImmutable $endDate,
        private ?\DateTimeImmutable $renewalDate,
        private ?\DateTimeImmutable $cancelledAt,
        private ?string $cancellationReason,
        private bool $autoRenew,
        private bool $isTrial,
        private int $trialDays,
        private int $failureCount,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        int $userId,
        int $planId,
        float $amount,
        bool $isTrial = false,
        int $trialDays = 0,
        bool $autoRenew = true,
    ): self {
        $now = new \DateTimeImmutable();
        return new self(
            id: null,
            userId: $userId,
            planId: $planId,
            amount: $amount,
            status: $isTrial ? SubscriptionStatus::Trial : SubscriptionStatus::Pending,
            startDate: $isTrial ? $now : null,
            endDate: null,
            renewalDate: null,
            cancelledAt: null,
            cancellationReason: null,
            autoRenew: $autoRenew,
            isTrial: $isTrial,
            trialDays: $trialDays,
            failureCount: 0,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function reconstitute(
        int $id,
        int $userId,
        int $planId,
        float $amount,
        string $status,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?\DateTimeImmutable $renewalDate,
        ?\DateTimeImmutable $cancelledAt,
        ?string $cancellationReason,
        bool $autoRenew,
        bool $isTrial,
        int $trialDays,
        int $failureCount,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            planId: $planId,
            amount: $amount,
            status: SubscriptionStatus::from($status),
            startDate: $startDate,
            endDate: $endDate,
            renewalDate: $renewalDate,
            cancelledAt: $cancelledAt,
            cancellationReason: $cancellationReason,
            autoRenew: $autoRenew,
            isTrial: $isTrial,
            trialDays: $trialDays,
            failureCount: $failureCount,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function activate(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): void
    {
        $this->assertCanTransitionTo(SubscriptionStatus::Active);
        $this->status = SubscriptionStatus::Active;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->renewalDate = $endDate;
    }

    public function renew(\DateTimeImmutable $newEndDate): void
    {
        if ($this->status !== SubscriptionStatus::Active) {
            throw new \RuntimeException('Only active subscriptions can be renewed');
        }
        $this->endDate = $newEndDate;
        $this->renewalDate = $newEndDate;
        $this->failureCount = 0;
    }

    public function suspend(string $reason = 'Payment failure'): void
    {
        $this->assertCanTransitionTo(SubscriptionStatus::Suspended);
        $this->status = SubscriptionStatus::Suspended;
        $this->cancellationReason = $reason;
    }

    public function cancel(?string $reason = null): void
    {
        $this->assertCanTransitionTo(SubscriptionStatus::Cancelled);
        $this->status = SubscriptionStatus::Cancelled;
        $this->cancelledAt = new \DateTimeImmutable();
        $this->cancellationReason = $reason;
    }

    public function reactivate(): void
    {
        if ($this->status !== SubscriptionStatus::Suspended && $this->status !== SubscriptionStatus::Expired) {
            throw new \RuntimeException('Only suspended or expired subscriptions can be reactivated');
        }
        $this->status = SubscriptionStatus::Active;
        $this->failureCount = 0;
    }

    public function markPaymentFailed(): void
    {
        $this->failureCount++;
    }

    public function isOverdue(): bool
    {
        return $this->renewalDate !== null && $this->renewalDate < new \DateTimeImmutable();
    }

    private function assertCanTransitionTo(SubscriptionStatus $target): void
    {
        if (!$this->status->canTransitionTo($target)) {
            throw new \RuntimeException("Cannot transition from {$this->status->value} to {$target->value}");
        }
    }

    public function id(): ?int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function planId(): int { return $this->planId; }
    public function amount(): float { return $this->amount; }
    public function status(): SubscriptionStatus { return $this->status; }
    public function startDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function endDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function renewalDate(): ?\DateTimeImmutable { return $this->renewalDate; }
    public function cancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function cancellationReason(): ?string { return $this->cancellationReason; }
    public function autoRenew(): bool { return $this->autoRenew; }
    public function isTrial(): bool { return $this->isTrial; }
    public function trialDays(): int { return $this->trialDays; }
    public function failureCount(): int { return $this->failureCount; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'plan_id' => $this->planId,
            'amount' => $this->amount,
            'status' => $this->status->value,
            'start_date' => $this->startDate?->format(\DateTimeInterface::ATOM),
            'end_date' => $this->endDate?->format(\DateTimeInterface::ATOM),
            'renewal_date' => $this->renewalDate?->format(\DateTimeInterface::ATOM),
            'cancelled_at' => $this->cancelledAt?->format(\DateTimeInterface::ATOM),
            'cancellation_reason' => $this->cancellationReason,
            'auto_renew' => $this->autoRenew,
            'is_trial' => $this->isTrial,
            'trial_days' => $this->trialDays,
            'failure_count' => $this->failureCount,
        ];
    }
}
