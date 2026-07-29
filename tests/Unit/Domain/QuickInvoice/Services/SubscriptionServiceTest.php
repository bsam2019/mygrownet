<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Domain\QuickInvoice\Services\QuickInvoiceBillingIntegration;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class SubscriptionServiceTest extends TestCase
{
    private SubscriptionRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $subscriptionRepo;
    private SubscriptionTierRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $tierRepo;
    private AdminSettingRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $adminRepo;
    private UsageTrackingRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $usageRepo;
    private QuickInvoiceBillingIntegration&\PHPUnit\Framework\MockObject\Stub $billing;
    private SubscriptionService $service;

    protected function setUp(): void
    {
        $this->subscriptionRepo = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->tierRepo = $this->createStub(SubscriptionTierRepositoryInterface::class);
        $this->adminRepo = $this->createStub(AdminSettingRepositoryInterface::class);
        $this->usageRepo = $this->createStub(UsageTrackingRepositoryInterface::class);
        $this->billing = $this->createStub(QuickInvoiceBillingIntegration::class);

        $this->service = new SubscriptionService(
            $this->subscriptionRepo,
            $this->tierRepo,
            $this->adminRepo,
            $this->usageRepo,
            $this->billing,
        );
    }

    #[Test]
    public function get_plans_returns_mapped_tiers(): void
    {
        $this->tierRepo
            ->method('findAllActive')
            ->willReturn([
                ['id' => 1, 'name' => 'Free', 'price' => '0.00', 'currency' => 'ZMW', 'documents_per_month' => 10, 'features' => ['basic'], 'is_active' => true],
                ['id' => 2, 'name' => 'Pro', 'price' => '99.00', 'currency' => 'USD', 'documents_per_month' => 100, 'features' => ['pro'], 'is_active' => true],
            ]);

        $plans = $this->service->getPlans();
        $this->assertCount(2, $plans);
        $this->assertSame('Free', $plans[0]['name']);
        $this->assertTrue($plans[0]['is_free']);
        $this->assertSame('Pro', $plans[1]['name']);
        $this->assertFalse($plans[1]['is_free']);
        $this->assertSame(99.0, $plans[1]['price']);
    }

    #[Test]
    public function get_plans_empty(): void
    {
        $this->tierRepo
            ->method('findAllActive')
            ->willReturn([]);

        $this->assertSame([], $this->service->getPlans());
    }

    #[Test]
    public function can_create_document_when_subscription_active_and_under_limit(): void
    {
        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => true,
                'tier' => ['documents_per_month' => 10],
            ]);

        $this->usageRepo
            ->method('getUserMonthlyUsage')
            ->willReturn(5);

        $this->assertTrue($this->service->canCreateDocument(1));
    }

    #[Test]
    public function can_create_document_when_inactive_returns_false(): void
    {
        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => false,
                'tier' => ['documents_per_month' => 10],
            ]);

        $this->assertFalse($this->service->canCreateDocument(1));
    }

    #[Test]
    public function can_create_document_when_over_limit_returns_false(): void
    {
        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => true,
                'tier' => ['documents_per_month' => 10],
            ]);

        $this->usageRepo
            ->method('getUserMonthlyUsage')
            ->willReturn(10);

        $this->assertFalse($this->service->canCreateDocument(1));
    }

    #[Test]
    public function can_create_document_when_unlimited(): void
    {
        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => true,
                'tier' => ['documents_per_month' => -1],
            ]);

        $this->usageRepo
            ->method('getUserMonthlyUsage')
            ->willReturn(999);

        $this->assertTrue($this->service->canCreateDocument(1));
    }

    #[Test]
    public function has_reached_limit_returns_null_when_limits_disabled(): void
    {
        $this->adminRepo
            ->method('isUsageLimitsEnabled')
            ->willReturn(false);

        $this->assertNull($this->service->hasReachedLimit(1));
    }

    #[Test]
    public function has_reached_limit_returns_info_when_over_limit(): void
    {
        $this->adminRepo
            ->method('isUsageLimitsEnabled')
            ->willReturn(true);

        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => true,
                'tier' => ['name' => 'Free', 'documents_per_month' => 5],
            ]);

        $this->usageRepo
            ->method('getUserMonthlyUsage')
            ->willReturn(5);

        $info = $this->service->hasReachedLimit(1);
        $this->assertIsArray($info);
        $this->assertSame('Free', $info['tier_name']);
        $this->assertSame(0, $info['remaining_documents']);
    }

    #[Test]
    public function has_reached_limit_returns_null_when_under_limit(): void
    {
        $this->adminRepo
            ->method('isUsageLimitsEnabled')
            ->willReturn(true);

        $this->subscriptionRepo
            ->method('getOrCreateFreeSubscription')
            ->willReturn([
                'is_active' => true,
                'tier' => ['name' => 'Free', 'documents_per_month' => 10],
            ]);

        $this->usageRepo
            ->method('getUserMonthlyUsage')
            ->willReturn(3);

        $this->assertNull($this->service->hasReachedLimit(1));
    }

    #[Test]
    public function track_usage_increments_when_subscription_exists(): void
    {
        $this->subscriptionRepo
            ->method('getCurrentSubscription')
            ->willReturn(['id' => 'sub-1']);

        $this->subscriptionRepo
            ->expects($this->once())
            ->method('incrementUsage')
            ->with('sub-1');

        $this->service->trackUsage(1);
    }

    #[Test]
    public function track_usage_does_nothing_when_no_subscription(): void
    {
        $this->subscriptionRepo
            ->method('getCurrentSubscription')
            ->willReturn(null);

        $this->subscriptionRepo
            ->expects($this->never())
            ->method('incrementUsage');

        $this->service->trackUsage(1);
    }

    #[Test]
    public function initiate_upgrade_tier_not_found_throws(): void
    {
        $this->tierRepo
            ->method('findById')
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->initiateUpgrade(1, 999);
    }

    #[Test]
    public function initiate_upgrade_free_tier_throws(): void
    {
        $this->tierRepo
            ->method('findById')
            ->willReturn(['id' => 1, 'name' => 'Free', 'price' => '0.00', 'currency' => 'ZMW']);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->initiateUpgrade(1, 1);
    }

    #[Test]
    public function get_admin_stats_counts_tiers(): void
    {
        $this->subscriptionRepo
            ->method('findAllActive')
            ->willReturn([
                ['tier' => ['name' => 'Free', 'price' => '0'], 'last_payment_at' => null],
                ['tier' => ['name' => 'Pro', 'price' => '99'], 'last_payment_at' => '2026-07-01'],
                ['tier' => ['name' => 'Free', 'price' => '0'], 'last_payment_at' => null],
            ]);

        $this->subscriptionRepo
            ->method('sumLastPaymentAmount')
            ->willReturn(99.0);

        $stats = $this->service->getAdminStats();
        $this->assertSame(3, $stats['total_subscriptions']);
        $this->assertSame(0, $stats['on_trial']);
        $this->assertSame(1, $stats['paid']);
        $this->assertSame(2, $stats['free']);
        $this->assertSame(99.0, $stats['total_revenue']);
    }
}
