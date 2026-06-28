<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\ValueObjects\SubscriptionId;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use App\Domain\Module\ValueObjects\Money;

/**
 * Module Subscription Service
 * 
 * Domain service for managing module subscriptions.
 */
class ModuleSubscriptionService
{
    public function __construct(
        private readonly ModuleSubscriptionRepositoryInterface $repository
    ) {}

    public function subscribe(
        int $userId,
        ModuleId $moduleId,
        SubscriptionTier $tier,
        Money $amount,
        string $billingCycle = 'monthly'
    ): ModuleSubscription {
        // Check if subscription already exists
        $existing = $this->repository->findByUserAndModule($userId, $moduleId);
        
        if ($existing && $existing->isActive()) {
            throw new \DomainException('User already has an active subscription to this module');
        }

        $subscription = ModuleSubscription::create(
            id: SubscriptionId::fromInt($this->generateId()),
            userId: $userId,
            moduleId: $moduleId,
            tier: $tier,
            amount: $amount,
            billingCycle: $billingCycle
        );

        $this->repository->save($subscription);

        return $subscription;
    }

    public function startTrial(
        int $userId,
        ModuleId $moduleId,
        SubscriptionTier $tier,
        int $trialDays = 14
    ): ModuleSubscription {
        // Check if subscription already exists
        $existing = $this->repository->findByUserAndModule($userId, $moduleId);
        
        if ($existing) {
            throw new \DomainException('User already has a subscription to this module');
        }

        $subscription = ModuleSubscription::createTrial(
            id: SubscriptionId::fromInt($this->generateId()),
            userId: $userId,
            moduleId: $moduleId,
            tier: $tier,
            trialDays: $trialDays
        );

        $this->repository->save($subscription);

        return $subscription;
    }

    public function cancel(int $userId, ModuleId $moduleId): void
    {
        $subscription = $this->repository->findByUserAndModule($userId, $moduleId);
        
        if (!$subscription) {
            throw new \DomainException('Subscription not found');
        }

        $subscription->cancel();
        $this->repository->save($subscription);
    }

    public function upgrade(
        int $userId,
        ModuleId $moduleId,
        SubscriptionTier $newTier,
        Money $newAmount
    ): ModuleSubscription {
        $subscription = $this->repository->findByUserAndModule($userId, $moduleId);
        
        if (!$subscription) {
            throw new \DomainException('Subscription not found');
        }

        if (!$subscription->isActive()) {
            throw new \DomainException('Cannot upgrade inactive subscription');
        }

        $subscription->upgradeTier($newTier, $newAmount);
        $this->repository->save($subscription);

        return $subscription;
    }

    public function convertFromTrial(
        int $userId,
        ModuleId $moduleId,
        Money $amount,
        string $billingCycle
    ): ModuleSubscription {
        $subscription = $this->repository->findByUserAndModule($userId, $moduleId);
        
        if (!$subscription) {
            throw new \DomainException('Subscription not found');
        }

        $subscription->convertFromTrial($amount, $billingCycle);
        $this->repository->save($subscription);

        return $subscription;
    }

    public function renewSubscription(SubscriptionId $subscriptionId): void
    {
        $subscription = $this->repository->findById($subscriptionId);
        
        if (!$subscription) {
            throw new \DomainException('Subscription not found');
        }

        $subscription->renew();
        $this->repository->save($subscription);
    }

    public function processExpiredSubscriptions(): int
    {
        $expired = $this->repository->findExpired();
        $count = 0;

        foreach ($expired as $subscription) {
            if ($subscription->isAutoRenew()) {
                try {
                    $this->renewSubscription($subscription->getId());
                    $count++;
                } catch (\Exception $e) {
                    // Log error and suspend subscription
                    $subscription->suspend();
                    $this->repository->save($subscription);
                }
            } else {
                $subscription->suspend();
                $this->repository->save($subscription);
            }
        }

        return $count;
    }

    private function generateId(): int
    {
        // This would typically use a sequence or auto-increment
        // For now, return a placeholder
        return time();
    }
}
