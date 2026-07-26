<?php

namespace App\Infrastructure\Contracts\Module;

use App\Domain\Module\Contracts\SubscriptionProvider;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;

class SubscriptionProviderImpl implements SubscriptionProvider
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function capability(): string
    {
        return 'module.subscription';
    }

    public function getUserTier(User $user, string $moduleId = 'growfinance'): string
    {
        return $this->subscriptionService->getUserTier($user, $moduleId);
    }

    public function hasFeature(User $user, string $feature, string $moduleId = 'growfinance'): bool
    {
        return $this->subscriptionService->hasFeature($user, $feature, $moduleId);
    }

    public function getUserLimits(User $user, string $moduleId = 'growfinance'): array
    {
        return $this->subscriptionService->getUserLimits($user, $moduleId);
    }

    public function clearCache(User $user, string $moduleId = 'growfinance'): void
    {
        $this->subscriptionService->clearCache($user, $moduleId);
    }
}
