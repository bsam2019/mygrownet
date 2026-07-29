<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Services\GrowBuilderBillingIntegration;
use App\Domain\PlatformBilling\Entities\SubscriptionStatus;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrowBuilderBillingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private GrowBuilderBillingIntegration $integration;
    private PlanRepositoryInterface $plans;
    private SubscriptionRepositoryInterface $subscriptions;
    private TransactionRepositoryInterface $transactions;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->integration = app(GrowBuilderBillingIntegration::class);
        $this->plans = app(PlanRepositoryInterface::class);
        $this->subscriptions = app(SubscriptionRepositoryInterface::class);
        $this->transactions = app(TransactionRepositoryInterface::class);
        $this->user = User::factory()->create();
    }

    public function test_ensure_plan_exists_creates_plan(): void
    {
        $plan = $this->integration->ensurePlanExists();

        $this->assertNotNull($plan->id());
        $this->assertEquals('growbuilder-subscription', $plan->slug());
        $this->assertEquals('GrowBuilder Subscription', $plan->name());
    }

    public function test_ensure_plan_exists_returns_existing(): void
    {
        $first = $this->integration->ensurePlanExists();
        $second = $this->integration->ensurePlanExists();

        $this->assertEquals($first->id(), $second->id());
        $this->assertCount(1, $this->plans->findAll());
    }

    public function test_process_payment_creates_transaction_and_subscription(): void
    {
        $result = $this->integration->processPayment(
            userId: $this->user->id,
            organizationId: 1,
            amount: 49.99,
            tier: 'basic',
            moduleSubscriptionId: 100,
        );

        $this->assertArrayHasKey('platform_subscription_id', $result);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertNotNull($result['platform_subscription_id']);
        $this->assertNotNull($result['transaction_id']);

        $subscription = $this->subscriptions->findById($result['platform_subscription_id']);
        $this->assertNotNull($subscription);

        $transaction = $this->transactions->findById($result['transaction_id']);
        $this->assertNotNull($transaction);
    }

    public function test_process_payment_with_trial(): void
    {
        $result = $this->integration->processPayment(
            userId: $this->user->id,
            organizationId: 1,
            amount: 0.0,
            tier: 'trial',
            moduleSubscriptionId: 101,
        );

        $subscription = $this->subscriptions->findById($result['platform_subscription_id']);
        $this->assertNotNull($subscription);
    }

    public function test_process_payment_stores_metadata(): void
    {
        $result = $this->integration->processPayment(
            userId: $this->user->id,
            organizationId: 5,
            amount: 99.99,
            tier: 'premium',
            moduleSubscriptionId: 200,
        );

        $txn = $this->transactions->findById($result['transaction_id']);
        $this->assertNotNull($txn);
        $metadata = $txn->toArray()['metadata'] ?? [];
        $this->assertEquals('growbuilder_subscription', $metadata['source']);
        $this->assertEquals('premium', $metadata['tier']);
        $this->assertEquals(200, $metadata['module_subscription_id']);
    }

    public function test_process_payment_activates_subscription_when_paid(): void
    {
        $result = $this->integration->processPayment(
            userId: $this->user->id,
            organizationId: 1,
            amount: 49.99,
            tier: 'basic',
            moduleSubscriptionId: 300,
        );

        $subscription = $this->subscriptions->findById($result['platform_subscription_id']);
        $this->assertEquals(SubscriptionStatus::Active, $subscription->status());
    }
}
