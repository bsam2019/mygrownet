<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\PaymentTransaction;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;

class QuickInvoiceBillingIntegration
{
    private const PLAN_SLUG = 'quickinvoice-subscription';

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
            name: 'QuickInvoice Subscription',
            slug: self::PLAN_SLUG,
            monthlyPrice: 0,
            annualPrice: 0,
            siteLimit: 0,
            storageLimitMb: 0,
            teamMemberLimit: 0,
            clientLimit: null,
            features: ['quickinvoice_subscription' => true],
            sortOrder: 0,
        );
    }

    public function processPayment(
        int $userId,
        int $organizationId,
        float $amount,
        string $tierName,
        int $subscriptionId,
    ): array {
        $plan = $this->ensurePlanExists();
        $now = new \DateTimeImmutable();

        $platformSubscription = $this->billing->createSubscription(
            userId: $userId,
            planId: $plan->id(),
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
                'source' => 'quickinvoice_upgrade',
                'tier_name' => $tierName,
                'billing_cycle' => 'monthly',
                'quickinvoice_subscription_id' => $subscriptionId,
                'platform_subscription_id' => $platformSubscription->id(),
            ],
        );

        $transaction->markCompleted(
            providerTransactionId: 'internal-' . $now->getTimestamp(),
            reference: 'QINV-' . $userId . '-' . $now->getTimestamp(),
        );

        $this->transactions->save($transaction);
        $this->billing->activateSubscription($platformSubscription->id(), 1);

        return [
            'platform_subscription_id' => $platformSubscription->id(),
            'transaction_id' => $transaction->id(),
        ];
    }
}
