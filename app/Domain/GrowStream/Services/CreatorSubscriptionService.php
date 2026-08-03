<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\CreatorSubscriptionRepositoryInterface;

class CreatorSubscriptionService
{
    public function __construct(
        private CreatorSubscriptionRepositoryInterface $subscriptionRepo,
    ) {}

    public function subscribe(int $userId, int $creatorId, float $priceMonthly, ?string $providerReference = null): array
    {
        $existing = $this->subscriptionRepo->activeForUserAndCreator($userId, $creatorId);
        if ($existing !== null) {
            return $existing->toArray();
        }

        $subscription = $this->subscriptionRepo->create([
            'user_id' => $userId,
            'creator_id' => $creatorId,
            'price_monthly' => $priceMonthly,
            'currency' => 'ZMW',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'provider_reference' => $providerReference,
        ]);

        return $subscription->toArray();
    }

    public function cancel(int $userId, int $creatorId): void
    {
        $subscription = $this->subscriptionRepo->activeForUserAndCreator($userId, $creatorId);
        if ($subscription === null) {
            return;
        }

        $this->subscriptionRepo->update($subscription, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function isSubscribed(int $userId, int $creatorId): bool
    {
        return $this->subscriptionRepo->isSubscribed($userId, $creatorId);
    }

    public function subscriberCount(int $creatorId): int
    {
        return $this->subscriptionRepo->subscriberCount($creatorId);
    }
}
