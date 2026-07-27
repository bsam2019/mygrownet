<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Services;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class GrowNetBillingIntegration
{
    private const PLAN_SLUG = 'grownet-starter-kit';

    public function __construct(
        private PlanRepositoryInterface $plans,
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
            name: 'GrowNet Starter Kit',
            slug: self::PLAN_SLUG,
            monthlyPrice: 0,
            annualPrice: 0,
            siteLimit: 0,
            storageLimitMb: 0,
            teamMemberLimit: 0,
            clientLimit: null,
            features: ['grownet_starter_kit' => true],
            sortOrder: 0,
        );
    }

    public function processPayment(
        int $userId,
        int $organizationId,
        float $amount,
        string $tier,
        int $purchaseId,
    ): array {
        $plan = $this->ensurePlanExists();
        $now = new \DateTimeImmutable();

        $txn = PaymentTransaction::create(
            organizationId: $organizationId,
            amount: $amount,
            currency: 'ZMW',
            paymentMethod: PaymentMethod::Wallet,
            provider: 'internal',
            metadata: [
                'source' => 'grownet_starter_kit',
                'tier' => $tier,
                'purchase_id' => $purchaseId,
            ],
        );

        $txn->markCompleted(
            providerTransactionId: 'internal-' . $now->getTimestamp(),
            reference: 'SK-' . $purchaseId . '-' . $now->getTimestamp(),
        );

        $this->transactions->save($txn);

        $platformSubscription = $this->billing->createSubscription(
            userId: $userId,
            planId: $plan->id(),
            amount: $amount,
            isTrial: false,
        );

        $this->billing->activateSubscription($platformSubscription->id(), 1);

        return [
            'platform_subscription_id' => $platformSubscription->id(),
            'transaction_id' => $txn->id(),
        ];
    }
}
