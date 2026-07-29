<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\Services;

use App\Domain\PlatformBilling\Entities\Subscription;
use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\Storage\Services\StorageBillingService;
use PHPUnit\Framework\TestCase;

final class StorageBillingServiceTest extends TestCase
{
    private PlanRepositoryInterface $plans;
    private SubscriptionRepositoryInterface $subscriptions;
    private BillingService $billing;
    private TransactionRepositoryInterface $transactions;
    private StorageBillingService $service;

    protected function setUp(): void
    {
        $this->plans = $this->createMock(PlanRepositoryInterface::class);
        $this->subscriptions = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->billing = $this->createMock(BillingService::class);
        $this->transactions = $this->createMock(TransactionRepositoryInterface::class);

        $this->service = new StorageBillingService(
            $this->plans,
            $this->subscriptions,
            $this->billing,
            $this->transactions,
        );
    }

    public function test_ensure_plan_exists_returns_existing_plan(): void
    {
        $plan = SubscriptionPlan::create('GrowBackup Storage', 'growbackup-storage', 0, 0, 0, 0, 0);

        $this->plans
            ->expects($this->once())
            ->method('findBySlug')
            ->with('growbackup-storage')
            ->willReturn($plan);

        $result = $this->service->ensurePlanExists();

        $this->assertSame($plan, $result);
    }

    public function test_ensure_plan_exists_creates_plan_when_not_found(): void
    {
        $plan = SubscriptionPlan::create('GrowBackup Storage', 'growbackup-storage', 0, 0, 0, 0, 0);

        $this->plans
            ->expects($this->once())
            ->method('findBySlug')
            ->with('growbackup-storage')
            ->willReturn(null);

        $this->billing
            ->expects($this->once())
            ->method('createPlan')
            ->with(
                'GrowBackup Storage',
                'growbackup-storage',
                0, 0, 0, 0, 0,
                null,
                ['storage_subscription' => true],
                0,
            )
            ->willReturn($plan);

        $result = $this->service->ensurePlanExists();

        $this->assertSame($plan, $result);
    }

    public function test_process_plan_upgrade_creates_subscription_and_transaction(): void
    {
        $plan = SubscriptionPlan::reconstitute(1, 'GrowBackup Storage', 'growbackup-storage', 0, 0, 0, 0, 0, null, ['storage_subscription' => true], true, 0, new \DateTimeImmutable(), new \DateTimeImmutable());

        $subscription = Subscription::reconstitute(
            id: 1,
            userId: 42,
            planId: 1,
            amount: 200.0,
            status: 'pending',
            startDate: null,
            endDate: null,
            renewalDate: null,
            cancelledAt: null,
            cancellationReason: null,
            autoRenew: true,
            isTrial: false,
            trialDays: 0,
            failureCount: 0,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
        $activatedSubscription = Subscription::reconstitute(
            id: 1,
            userId: 42,
            planId: 1,
            amount: 200.0,
            status: 'active',
            startDate: new \DateTimeImmutable(),
            endDate: (new \DateTimeImmutable())->modify('+1 month'),
            renewalDate: null,
            cancelledAt: null,
            cancellationReason: null,
            autoRenew: true,
            isTrial: false,
            trialDays: 0,
            failureCount: 0,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->plans
            ->expects($this->once())
            ->method('findBySlug')
            ->with('growbackup-storage')
            ->willReturn($plan);

        $this->billing
            ->expects($this->once())
            ->method('createSubscription')
            ->with(42, 1, 200.0, false)
            ->willReturn($subscription);

        $this->transactions
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn($txn) => $txn instanceof \App\Domain\PlatformPayments\Entities\PaymentTransaction));

        $this->billing
            ->expects($this->once())
            ->method('activateSubscription')
            ->with(1, 1);

        $this->subscriptions
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($activatedSubscription);

        $result = $this->service->processPlanUpgrade(
            userId: 42,
            organizationId: 10,
            planSlug: 'pro-monthly',
            amount: 200.0,
            billingCycle: 'monthly',
        );

        $this->assertEquals(1, $result['subscription_id']);
        $this->assertNull($result['transaction_id']);
        $this->assertEquals(200.0, $result['amount']);
        $this->assertEquals('active', $result['status']);
        $this->assertNotNull($result['end_date']);
    }
}
