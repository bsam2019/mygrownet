<?php

namespace App\Domain\Storage\Services;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class StorageBillingService
{
    private const PLAN_SLUG = 'growbackup-storage';

    public function __construct(
        private PlanRepositoryInterface $plans,
        private SubscriptionRepositoryInterface $subscriptions,
        private BillingService $billing,
        private TransactionRepositoryInterface $transactions,
    ) {}

    public function ensurePlanExists(): SubscriptionPlan
    {
        $plan = $this->plans->findBySlug(self::PLAN_SLUG);
        if ($plan) {
            return $plan;
        }

        return $this->billing->createPlan(
            name: 'GrowBackup Storage',
            slug: self::PLAN_SLUG,
            monthlyPrice: 0,
            annualPrice: 0,
            siteLimit: 0,
            storageLimitMb: 0,
            teamMemberLimit: 0,
            clientLimit: null,
            features: ['storage_subscription' => true],
            sortOrder: 0,
        );
    }

    public function processPlanUpgrade(
        int $userId,
        int $organizationId,
        string $planSlug,
        float $amount,
        string $billingCycle,
    ): array {
        $platformPlan = $this->ensurePlanExists();
        $now = new \DateTimeImmutable();

        $subscription = $this->billing->createSubscription(
            userId: $userId,
            planId: $platformPlan->id(),
            amount: $amount,
            isTrial: false,
        );

        $transaction = PaymentTransaction::create(
            organizationId: $organizationId,
            amount: $amount,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::Wallet,
            provider: 'internal',
            metadata: [
                'source' => 'storage_upgrade',
                'plan_slug' => $planSlug,
                'billing_cycle' => $billingCycle,
                'platform_subscription_id' => $subscription->id(),
            ],
        );

        $transaction->markCompleted(
            providerTransactionId: 'internal-' . $now->getTimestamp(),
            reference: 'STORAGE-' . $userId . '-' . $now->getTimestamp(),
        );

        $this->transactions->save($transaction);
        $this->billing->activateSubscription($subscription->id(), $billingCycle === 'annual' ? 12 : 1);

        $activated = $this->subscriptions->findById($subscription->id());

        return [
            'subscription_id' => $activated->id(),
            'transaction_id' => $transaction->id(),
            'amount' => $amount,
            'status' => $activated->status()->value,
            'end_date' => $activated->endDate()?->format(\DateTimeInterface::ATOM),
        ];
    }
}
