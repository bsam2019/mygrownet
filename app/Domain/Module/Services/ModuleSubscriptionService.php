<?php

namespace App\Domain\Module\Services;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use App\Domain\Module\ValueObjects\SubscriptionId;
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

        $subscription = $existing ?? new ModuleSubscription(
            id: null,
            userId: $userId,
            moduleId: $moduleId->value(),
            subscriptionTier: $tier->value(),
            status: 'pending',
            startedAt: new \DateTimeImmutable(),
            trialEndsAt: null,
            expiresAt: null,
            cancelledAt: null,
            autoRenew: true,
            billingCycle: $billingCycle,
            amount: $amount,
            userLimit: null,
            storageLimitMb: null,
        );

        $subscription->setStatus('active');
        $subscription->setExpiresAt($subscription->getExpiresAt() ?? ModuleSubscription::calculateExpirationFor($billingCycle));
        $subscription->setSubscriptionTier($tier->value());

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
            userId: $userId,
            moduleId: $moduleId->value(),
            subscriptionTier: $tier->value(),
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
        $subscription = $this->repository->findById((string) $subscriptionId->value());
        
        if (!$subscription) {
            throw new \DomainException('Subscription not found');
        }

        $subscription->renew();
        $this->repository->save($subscription);
    }

    /**
     * Start a checkout for a module subscription.
     *
     * Creates (or reuses) a pending subscription row linked to a payment
     * provider reference. The subscription is activated only after the
     * payment completes (see activateOnPayment).
     */
    public function startCheckout(
        int $userId,
        ModuleId $moduleId,
        SubscriptionTier $tier,
        Money $amount,
        string $billingCycle = 'monthly'
    ): ModuleSubscription {
        $existing = $this->repository->findByUserAndModule($userId, $moduleId);

        if ($existing && $existing->isActive()) {
            throw new \DomainException('User already has an active subscription to this module');
        }

        $subscription = $existing
            ?? new ModuleSubscription(
                id: null,
                userId: $userId,
                moduleId: $moduleId->value(),
                subscriptionTier: $tier->value(),
                status: 'pending',
                startedAt: new \DateTimeImmutable(),
                trialEndsAt: null,
                expiresAt: null,
                cancelledAt: null,
                autoRenew: true,
                billingCycle: $billingCycle,
                amount: $amount,
                userLimit: null,
                storageLimitMb: null,
            );

        $subscription->setStatus('pending');
        $subscription->setExpiresAt(null);
        $subscription->setSubscriptionTier($tier->value());
        $subscription->setProviderReference($this->generatePaymentReference($subscription));

        $this->repository->save($subscription);

        return $subscription;
    }

    /**
     * Activate a pending subscription once payment has been confirmed.
     *
     * @return ModuleSubscription
     */
    public function activateOnPayment(string $providerReference): ModuleSubscription
    {
        $subscription = $this->repository->findByProviderReference($providerReference);

        if (!$subscription) {
            throw new \DomainException("No pending subscription found for payment reference [{$providerReference}]");
        }

        $subscription->markActive();
        $this->repository->save($subscription);

        return $subscription;
    }

    private function generatePaymentReference(ModuleSubscription $subscription): string
    {
        $id = $subscription->getId()?->value() ?? (int) (microtime(true) * 1000);
        return "sub_{$subscription->getUserId()}_{$id}";
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
}
