<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Services;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class GrowBuilderBillingIntegration
{
    private const PLAN_SLUG = 'growbuilder-subscription';

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
            name: 'GrowBuilder Subscription',
            slug: self::PLAN_SLUG,
            monthlyPrice: 0,
            annualPrice: 0,
            siteLimit: 0,
            storageLimitMb: 0,
            teamMemberLimit: 0,
            clientLimit: null,
            features: ['growbuilder_subscription' => true],
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
                'source' => 'growbuilder_subscription',
                'tier' => $tier,
                'module_subscription_id' => $moduleSubscriptionId,
            ],
        );

        $txn->markCompleted(
            providerTransactionId: 'internal-' . $now->getTimestamp() . '-' . $moduleSubscriptionId,
            reference: 'GB-' . $moduleSubscriptionId . '-' . $now->getTimestamp(),
        );

        $txn = $this->transactions->save($txn);

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
