<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Domain\QuickInvoice\Services\AdminDashboardService;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class AdminDashboardServiceTest extends TestCase
{
    private UsageTrackingRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $usageRepo;
    private SubscriptionRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $subscriptionRepo;
    private SubscriptionTierRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $tierRepo;
    private AdminSettingRepositoryInterface&\PHPUnit\Framework\MockObject\Stub $adminRepo;
    private SubscriptionService&\PHPUnit\Framework\MockObject\Stub $subscriptionService;
    private AdminDashboardService $service;

    protected function setUp(): void
    {
        $this->usageRepo = $this->createStub(UsageTrackingRepositoryInterface::class);
        $this->subscriptionRepo = $this->createStub(SubscriptionRepositoryInterface::class);
        $this->tierRepo = $this->createStub(SubscriptionTierRepositoryInterface::class);
        $this->adminRepo = $this->createStub(AdminSettingRepositoryInterface::class);
        $this->subscriptionService = $this->createStub(SubscriptionService::class);

        $this->service = new AdminDashboardService(
            $this->usageRepo,
            $this->subscriptionRepo,
            $this->tierRepo,
            $this->adminRepo,
            $this->subscriptionService,
        );
    }

    #[Test]
    public function get_dashboard_data_returns_all_sections(): void
    {
        $this->usageRepo
            ->method('getStats')
            ->willReturn(['total' => 10]);

        $this->usageRepo
            ->method('getRecentActivity')
            ->willReturn([]);

        $this->subscriptionRepo
            ->method('countDistinctUsers')
            ->willReturn(100);

        $this->subscriptionRepo
            ->method('countActive')
            ->willReturn(50);

        $this->subscriptionRepo
            ->method('findAllActive')
            ->willReturn([]);

        $this->adminRepo
            ->method('getMonetizationSettings')
            ->willReturn(['enabled' => true]);

        $this->adminRepo
            ->method('get')
            ->willReturn(['trial_days' => 30]);

        $this->subscriptionService
            ->method('getAdminStats')
            ->willReturn(['total_subscriptions' => 100]);

        $data = $this->service->getDashboardData();

        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('subscriptionStats', $data);
        $this->assertArrayHasKey('recentActivity', $data);
        $this->assertArrayHasKey('monetizationSettings', $data);
        $this->assertArrayHasKey('trialSettings', $data);
        $this->assertArrayHasKey('billingStats', $data);

        $this->assertSame(100, $data['subscriptionStats']['total_users']);
        $this->assertSame(50, $data['subscriptionStats']['active_subscriptions']);
    }

    #[Test]
    public function get_usage_analytics_defaults_to_30_days(): void
    {
        $this->usageRepo
            ->method('getDailyUsage')
            ->willReturn([]);

        $this->usageRepo
            ->method('getOverallStats')
            ->willReturn(['total' => 0]);

        $this->usageRepo
            ->method('getTopUsers')
            ->willReturn([]);

        $result = $this->service->getUsageAnalytics();
        $this->assertSame('30d', $result['period']);
    }

    #[Test]
    public function get_usage_analytics_7_days(): void
    {
        $this->usageRepo
            ->method('getDailyUsage')
            ->willReturn([]);

        $this->usageRepo
            ->method('getOverallStats')
            ->willReturn(['total' => 0]);

        $this->usageRepo
            ->method('getTopUsers')
            ->willReturn([]);

        $result = $this->service->getUsageAnalytics('7d');
        $this->assertSame('7d', $result['period']);
    }

    #[Test]
    public function get_usage_analytics_90_days(): void
    {
        $this->usageRepo
            ->method('getDailyUsage')
            ->willReturn([]);

        $this->usageRepo
            ->method('getOverallStats')
            ->willReturn(['total' => 0]);

        $this->usageRepo
            ->method('getTopUsers')
            ->willReturn([]);

        $result = $this->service->getUsageAnalytics('90d');
        $this->assertSame('90d', $result['period']);
    }

    #[Test]
    public function get_tiers_returns_mapped_data(): void
    {
        $this->tierRepo
            ->method('findAll')
            ->willReturn([
                ['id' => 1, 'name' => 'Free', 'price' => '0.00', 'currency' => 'ZMW', 'documents_per_month' => 5, 'features' => [], 'is_active' => true, 'created_at' => '2026-01-01'],
            ]);

        $tiers = $this->service->getTiers();
        $this->assertCount(1, $tiers);
        $this->assertSame('Free', $tiers[0]['name']);
        $this->assertSame(0.0, $tiers[0]['price']);
    }
}
