<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class BizBoostBillingIntegration
{
    private const PLAN_SLUG = 'bizboost-subscription';

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
            name: 'BizBoost Subscription',
            slug: self::PLAN_SLUG,
            monthlyPrice: 0,
            annualPrice: 0,
            siteLimit: 0,
            storageLimitMb: 0,
            teamMemberLimit: 0,
            clientLimit: null,
            features: ['bizboost_subscription' => true],
            sortOrder: 0,
        );
    }

    public function processPayment(
        int $userId,
        int $organizationId,
        float $amount,
        string $tier,
        int $moduleSubscriptionId,
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
                'source' => 'bizboost_subscription',
                'tier' => $tier,
                'module_subscription_id' => $moduleSubscriptionId,
            ],
        );

        $txn->markCompleted(
            providerTransactionId: 'internal-' . $now->getTimestamp() . '-' . $moduleSubscriptionId,
            reference: 'BIZ-' . $moduleSubscriptionId . '-' . $now->getTimestamp(),
        );

        $this->transactions->save($txn);

        $platformSubscription = $this->billing->createSubscription(
            userId: $userId,
            planId: $plan->id(),
            amount: $amount,
            isTrial: $amount === 0.0,
        );

        if ($amount > 0) {
            $this->billing->activateSubscription($platformSubscription->id(), 1);
        }

        return [
            'platform_subscription_id' => $platformSubscription->id(),
            'transaction_id' => $txn->id(),
        ];
    }
}
