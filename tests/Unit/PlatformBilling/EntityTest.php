<?php

namespace Tests\Unit\PlatformBilling;

use App\Domain\PlatformBilling\Entities\Invoice;
use App\Domain\PlatformBilling\Entities\InvoiceStatus;
use App\Domain\PlatformBilling\Entities\Subscription;
use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Entities\SubscriptionStatus;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    // --- SubscriptionPlan ---

    public function test_subscription_plan_create(): void
    {
        $plan = SubscriptionPlan::create(
            name: 'Basic Monthly',
            slug: 'basic-monthly',
            monthlyPrice: 29.99,
            annualPrice: 299.99,
            siteLimit: 1,
            storageLimitMb: 5000,
            teamMemberLimit: 5,
            clientLimit: null,
            features: ['invoicing', 'reports'],
        );

        $this->assertNull($plan->id());
        $this->assertEquals('Basic Monthly', $plan->name());
        $this->assertEquals(29.99, $plan->monthlyPrice());
        $this->assertEquals(299.99, $plan->annualPrice());
        $this->assertTrue($plan->isActive());
    }

    public function test_subscription_plan_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $plan = SubscriptionPlan::reconstitute(
            id: 1,
            name: 'Pro Annual',
            slug: 'pro-annual',
            monthlyPrice: 49.99,
            annualPrice: 499.99,
            siteLimit: 10,
            storageLimitMb: 50000,
            teamMemberLimit: 50,
            clientLimit: 100,
            features: ['all', 'api', 'support'],
            isActive: true,
            sortOrder: 1,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(1, $plan->id());
        $this->assertEquals('pro-annual', $plan->slug());
    }

    public function test_subscription_plan_deactivate(): void
    {
        $plan = SubscriptionPlan::create('Test', 'test', 10, 100, 1, 1000, 1);
        $plan->deactivate();
        $this->assertFalse($plan->isActive());
    }

    public function test_subscription_plan_activate(): void
    {
        $plan = SubscriptionPlan::create('Test', 'test', 10, 100, 1, 1000, 1);
        $plan->deactivate();
        $plan->activate();
        $this->assertTrue($plan->isActive());
    }

    // --- Subscription ---

    public function test_subscription_create_pending(): void
    {
        $sub = Subscription::create(
            userId: 1,
            planId: 1,
            amount: 29.99,
        );

        $this->assertNull($sub->id());
        $this->assertEquals(SubscriptionStatus::Pending, $sub->status());
        $this->assertEquals(29.99, $sub->amount());
    }

    public function test_subscription_create_trial(): void
    {
        $sub = Subscription::create(
            userId: 1,
            planId: 1,
            amount: 0,
            isTrial: true,
            trialDays: 14,
        );

        $this->assertEquals(SubscriptionStatus::Trial, $sub->status());
        $this->assertTrue($sub->isTrial());
        $this->assertEquals(14, $sub->trialDays());
        $this->assertNotNull($sub->startDate());
    }

    public function test_subscription_activate(): void
    {
        $sub = Subscription::create(1, 1, 29.99);
        $sub->activate(
            startDate: new \DateTimeImmutable('2026-01-01'),
            endDate: new \DateTimeImmutable('2027-01-01'),
        );

        $this->assertEquals(SubscriptionStatus::Active, $sub->status());
        $this->assertNotNull($sub->startDate());
        $this->assertNotNull($sub->endDate());
    }

    public function test_subscription_cancel(): void
    {
        $sub = Subscription::create(1, 1, 29.99);
        $sub->activate(
            startDate: new \DateTimeImmutable(),
            endDate: new \DateTimeImmutable('+1 year'),
        );

        $sub->cancel('No longer needed');

        $this->assertEquals(SubscriptionStatus::Cancelled, $sub->status());
        $this->assertNotNull($sub->cancelledAt());
        $this->assertEquals('No longer needed', $sub->cancellationReason());
    }

    public function test_subscription_suspend(): void
    {
        $sub = Subscription::create(1, 1, 29.99);
        $sub->activate(
            startDate: new \DateTimeImmutable(),
            endDate: new \DateTimeImmutable('+1 year'),
        );

        $sub->suspend('Payment failed');

        $this->assertEquals(SubscriptionStatus::Suspended, $sub->status());
    }

    public function test_subscription_renew(): void
    {
        $sub = Subscription::create(1, 1, 29.99);
        $sub->activate(
            startDate: new \DateTimeImmutable('2026-01-01'),
            endDate: new \DateTimeImmutable('2026-02-01'),
        );

        $sub->renew(new \DateTimeImmutable('2026-03-01'));
        $this->assertEquals(SubscriptionStatus::Active, $sub->status());
    }

    public function test_subscription_reactivate_after_suspend(): void
    {
        $sub = Subscription::create(1, 1, 29.99);
        $sub->activate(
            startDate: new \DateTimeImmutable(),
            endDate: new \DateTimeImmutable('+1 year'),
        );
        $sub->suspend('Non-payment');
        $sub->reactivate();

        $this->assertEquals(SubscriptionStatus::Active, $sub->status());
    }

    public function test_subscription_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $sub = Subscription::reconstitute(
            id: 5,
            userId: 1,
            planId: 2,
            amount: 49.99,
            status: 'active',
            startDate: $now,
            endDate: $now->modify('+1 year'),
            renewalDate: $now->modify('+1 year'),
            cancelledAt: null,
            cancellationReason: null,
            autoRenew: true,
            isTrial: false,
            trialDays: 0,
            failureCount: 0,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(5, $sub->id());
        $this->assertEquals(SubscriptionStatus::Active, $sub->status());
    }

    // --- SubscriptionStatus ---

    public function test_subscription_status_transitions(): void
    {
        $this->assertTrue(SubscriptionStatus::Pending->canTransitionTo(SubscriptionStatus::Active));
        $this->assertTrue(SubscriptionStatus::Pending->canTransitionTo(SubscriptionStatus::Cancelled));
        $this->assertFalse(SubscriptionStatus::Cancelled->canTransitionTo(SubscriptionStatus::Active));
        $this->assertTrue(SubscriptionStatus::Active->canTransitionTo(SubscriptionStatus::Suspended));
        $this->assertTrue(SubscriptionStatus::Suspended->canTransitionTo(SubscriptionStatus::Active));
    }

    // --- Invoice ---

    public function test_invoice_create_draft(): void
    {
        $inv = Invoice::create(
            subscriptionId: 1,
            organizationId: 1,
            amount: 99.99,
            currency: 'USD',
            dueDate: new \DateTimeImmutable('+30 days'),
        );

        $this->assertNull($inv->id());
        $this->assertEquals(InvoiceStatus::Draft, $inv->status());
        $this->assertEquals(99.99, $inv->amount());
    }

    public function test_invoice_issue(): void
    {
        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->issue('INV-0001');

        $this->assertEquals(InvoiceStatus::Issued, $inv->status());
        $this->assertEquals('INV-0001', $inv->invoiceNumber());
        $this->assertNotNull($inv->issuedAt());
    }

    public function test_invoice_mark_paid(): void
    {
        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->issue('INV-0002');
        $inv->markPaid(new \DateTimeImmutable());

        $this->assertEquals(InvoiceStatus::Paid, $inv->status());
        $this->assertNotNull($inv->paidAt());
    }

    public function test_invoice_mark_overdue(): void
    {
        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->issue('INV-0003');
        $inv->markOverdue();

        $this->assertEquals(InvoiceStatus::Overdue, $inv->status());
    }

    public function test_invoice_cancel_draft(): void
    {
        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->cancel();

        $this->assertEquals(InvoiceStatus::Cancelled, $inv->status());
    }

    public function test_invoice_cancel_issued(): void
    {
        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->issue('INV-0004');
        $inv->cancel();

        $this->assertEquals(InvoiceStatus::Cancelled, $inv->status());
    }

    public function test_invoice_cannot_cancel_paid(): void
    {
        $this->expectException(\RuntimeException::class);

        $inv = Invoice::create(1, 1, 99.99, 'USD', new \DateTimeImmutable('+30 days'));
        $inv->issue('INV-0005');
        $inv->markPaid(new \DateTimeImmutable());
        $inv->cancel();
    }

    public function test_invoice_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $inv = Invoice::reconstitute(
            id: 3,
            subscriptionId: 1,
            organizationId: 1,
            invoiceNumber: 'INV-0003',
            amount: 49.99,
            currency: 'USD',
            status: 'paid',
            issuedAt: $now,
            dueDate: $now,
            paidAt: $now,
            description: 'Monthly subscription',
            lineItems: [],
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(3, $inv->id());
        $this->assertEquals(InvoiceStatus::Paid, $inv->status());
    }
}
