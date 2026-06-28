<?php

namespace App\Application\DTOs;

use App\Domain\Module\Entities\ModuleSubscription;

class ModuleSubscriptionDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $userId,
        public readonly string $moduleId,
        public readonly string $tier,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $billingCycle,
        public readonly string $status,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $nextBillingDate,
        public readonly ?string $cancelledAt,
        public readonly ?string $cancellationReason,
        public readonly bool $autoRenew,
        public readonly ?array $metadata
    ) {}

    public static function fromEntity(ModuleSubscription $subscription): self
    {
        return new self(
            id: $subscription->getId()?->value() ?? '',
            userId: $subscription->getUserId(),
            moduleId: $subscription->getModuleId(),
            tier: $subscription->getSubscriptionTier(),
            amount: $subscription->getAmount()->getAmount(),
            currency: $subscription->getAmount()->getCurrency(),
            billingCycle: $subscription->getBillingCycle(),
            status: $subscription->getStatus(),
            startDate: $subscription->getStartedAt()->format('Y-m-d H:i:s'),
            endDate: $subscription->getExpiresAt()?->format('Y-m-d H:i:s'),
            nextBillingDate: $subscription->getExpiresAt()?->format('Y-m-d H:i:s'),
            cancelledAt: $subscription->getCancelledAt()?->format('Y-m-d H:i:s'),
            cancellationReason: null, // Not stored in entity
            autoRenew: $subscription->isAutoRenew(),
            metadata: null // Not stored in entity
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'module_id' => $this->moduleId,
            'tier' => $this->tier,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'billing_cycle' => $this->billingCycle,
            'status' => $this->status,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'next_billing_date' => $this->nextBillingDate,
            'cancelled_at' => $this->cancelledAt,
            'cancellation_reason' => $this->cancellationReason,
            'auto_renew' => $this->autoRenew,
            'metadata' => $this->metadata,
        ];
    }
}
