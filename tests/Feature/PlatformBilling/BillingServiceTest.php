<?php

namespace Tests\Feature\PlatformBilling;

use App\Domain\Core\Models\Organization;
use App\Domain\Core\Services\OutboxService;
use App\Domain\Core\Services\PlatformContextResolver;
use App\Domain\PlatformBilling\Entities\InvoiceStatus;
use App\Domain\PlatformBilling\Entities\SubscriptionStatus;
use App\Domain\PlatformBilling\Repositories\InvoiceRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BillingService $service;
    private PlanRepositoryInterface $plans;
    private SubscriptionRepositoryInterface $subscriptions;
    private InvoiceRepositoryInterface $invoices;
    private User $user;
    private User $anotherUser;
    private Organization $organization;
    private Organization $anotherOrganization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'billing-a@example.com']);
        $this->anotherUser = User::factory()->create(['email' => 'billing-b@example.com']);
        $this->organization = Organization::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Org',
            'slug' => 'test-org',
            'type' => 'business',
            'status' => 'active',
            'owner_id' => $this->user->id,
        ]);
        $this->anotherOrganization = Organization::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Org 2',
            'slug' => 'test-org-2',
            'type' => 'business',
            'status' => 'active',
            'owner_id' => $this->anotherUser->id,
        ]);

        $this->plans = $this->app->make(PlanRepositoryInterface::class);
        $this->subscriptions = $this->app->make(SubscriptionRepositoryInterface::class);
        $this->invoices = $this->app->make(InvoiceRepositoryInterface::class);

        $outbox = $this->app->make(OutboxService::class);
        $contextResolver = $this->app->make(PlatformContextResolver::class);

        $this->service = new BillingService(
            plans: $this->plans,
            subscriptions: $this->subscriptions,
            invoices: $this->invoices,
            outbox: $outbox,
            contextResolver: $contextResolver,
        );
    }

    // --- Plan Management ---

    public function test_create_plan(): void
    {
        $plan = $this->service->createPlan(
            name: 'Starter',
            slug: 'starter-v1',
            monthlyPrice: 19.99,
            annualPrice: 199.99,
            siteLimit: 1,
            storageLimitMb: 1000,
            teamMemberLimit: 3,
        );

        $this->assertNotNull($plan->id());
        $this->assertEquals('Starter', $plan->name());

        $found = $this->plans->findBySlug('starter-v1');
        $this->assertNotNull($found);
    }

    public function test_find_active_plans(): void
    {
        $this->service->createPlan('A', 'plan-a', 10, 100, 1, 1000, 1);
        $this->service->createPlan('B', 'plan-b', 20, 200, 5, 5000, 5);

        $active = $this->plans->findActive();
        $this->assertCount(2, $active);
    }

    // --- Subscription Lifecycle ---

    public function test_create_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);

        $sub = $this->service->createSubscription(
            userId: $this->user->id,
            planId: $plan->id(),
            amount: 49.99,
        );

        $this->assertNotNull($sub->id());
        $this->assertEquals(SubscriptionStatus::Pending, $sub->status());

        $found = $this->subscriptions->findById($sub->id());
        $this->assertNotNull($found);
    }

    public function test_activate_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);

        $activated = $this->service->activateSubscription($sub->id());
        $this->assertEquals(SubscriptionStatus::Active, $activated->status());
    }

    public function test_cancel_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());

        $canceled = $this->service->cancelSubscription($sub->id(), 'Not needed');
        $this->assertEquals(SubscriptionStatus::Cancelled, $canceled->status());
    }

    public function test_cancel_nonexistent_subscription_throws(): void
    {
        $this->expectException(\App\Domain\PlatformBilling\Exceptions\BillingException::class);
        $this->service->cancelSubscription(9999);
    }

    public function test_suspend_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());

        $suspended = $this->service->suspendSubscription($sub->id(), 'Non-payment');
        $this->assertEquals(SubscriptionStatus::Suspended, $suspended->status());
    }

    public function test_renew_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id(), 1);

        $renewed = $this->service->renewSubscription($sub->id(), 1);
        $this->assertEquals(SubscriptionStatus::Active, $renewed->status());
    }

    public function test_reactivate_subscription(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());
        $this->service->suspendSubscription($sub->id(), 'Non-payment');

        $reactivated = $this->service->reactivateSubscription($sub->id());
        $this->assertEquals(SubscriptionStatus::Active, $reactivated->status());
    }

    // --- Invoice Lifecycle ---

    public function test_issue_invoice(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());

        $inv = $this->service->issueInvoice(
            subscriptionId: $sub->id(),
            organizationId: $this->organization->id,
        );

        $this->assertNotNull($inv->id());
        $this->assertEquals(InvoiceStatus::Issued, $inv->status());
        $this->assertEquals(49.99, $inv->amount());
    }

    public function test_mark_invoice_paid(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());
        $inv = $this->service->issueInvoice($sub->id(), $this->organization->id);

        $paid = $this->service->markInvoicePaid($inv->id(), new \DateTimeImmutable());
        $this->assertEquals(InvoiceStatus::Paid, $paid->status());
    }

    public function test_process_overdue_invoices(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());
        $inv = $this->service->issueInvoice($sub->id(), $this->organization->id);

        $overdue = $this->service->processOverdueInvoices();
        $this->assertEquals(0, $overdue);

        $found = $this->invoices->findById($inv->id());
        $this->assertEquals(InvoiceStatus::Issued, $found->status());
    }

    public function test_handle_payment_failure(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());

        $this->service->handlePaymentFailure($sub->id(), 3);

        $found = $this->subscriptions->findById($sub->id());
        $this->assertEquals(1, $found->failureCount());
    }

    public function test_get_organization_invoices(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub1 = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub1->id());
        $this->service->issueInvoice($sub1->id(), $this->organization->id);

        $sub2 = $this->service->createSubscription($this->anotherUser->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub2->id());
        $this->service->issueInvoice($sub2->id(), $this->anotherOrganization->id);

        $org1Invoices = $this->invoices->findByOrganization($this->organization->id);
        $org2Invoices = $this->invoices->findByOrganization($this->anotherOrganization->id);

        $this->assertCount(1, $org1Invoices);
        $this->assertCount(1, $org2Invoices);
    }

    public function test_find_expiring_subscriptions(): void
    {
        $plan = $this->service->createPlan('Pro', 'pro', 49.99, 499.99, 10, 50000, 50);
        $sub = $this->service->createSubscription($this->user->id, $plan->id(), 49.99);
        $this->service->activateSubscription($sub->id());

        $expiring = $this->service->processExpiringSubscriptions(365);
        $this->assertContains($sub->id(), $expiring);
    }
}
