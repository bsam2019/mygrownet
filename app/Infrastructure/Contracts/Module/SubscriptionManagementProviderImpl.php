<?php

namespace App\Infrastructure\Contracts\Module;

use App\Domain\Module\Contracts\SubscriptionManagementProvider;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use App\Domain\Module\ValueObjects\Money;

class SubscriptionManagementProviderImpl implements SubscriptionManagementProvider
{
    public function __construct(
        private readonly ModuleSubscriptionService $moduleSubscriptionService
    ) {}

    public function capability(): string
    {
        return 'module.subscription_management';
    }

    public function subscribe(int $userId, string $moduleId, string $tier, string $billingCycle = 'monthly', float $amount = 0.0): array
    {
        $subscription = $this->moduleSubscriptionService->subscribe(
            userId: $userId,
            moduleId: ModuleId::fromString($moduleId),
            tier: SubscriptionTier::fromString($tier),
            amount: Money::fromFloat($amount),
            billingCycle: $billingCycle
        );
        return $subscription->toArray();
    }

    public function cancel(int $userId, string $moduleId): void
    {
        $this->moduleSubscriptionService->cancel(
            userId: $userId,
            moduleId: ModuleId::fromString($moduleId)
        );
    }
}
