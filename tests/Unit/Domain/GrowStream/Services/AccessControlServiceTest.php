<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Repositories\VideoViewRepositoryInterface;
use App\Domain\GrowStream\Services\AccessControlService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccessControlServiceTest extends TestCase
{
    private function makeService(string $tier, ?int $viewsUsed = 0, array $tierLimits = ['views_per_month' => 300]): AccessControlService
    {
        $subscriptionService = $this->createMock(SubscriptionService::class);
        $subscriptionService->method('getUserTier')
            ->willReturn($tier);

        $viewRepo = $this->createMock(VideoViewRepositoryInterface::class);
        $viewRepo->method('countPremiumViewsByUser')
            ->willReturn($viewsUsed ?? 0);

        $tierConfig = $this->createMock(TierConfigurationService::class);
        $tierConfig->method('getTierLimits')
            ->willReturn($tierLimits);

        return new AccessControlService($subscriptionService, $viewRepo, $tierConfig);
    }

    private function user(int $id = 1): User
    {
        $user = new User;
        $user->id = $id;

        return $user;
    }

    #[Test]
    public function free_content_is_accessible_without_user(): void
    {
        $service = $this->makeService('none');

        $this->assertTrue($service->userCanAccess(null, 'free'));
    }

    #[Test]
    public function premium_content_requires_user(): void
    {
        $service = $this->makeService('none');

        $this->assertFalse($service->userCanAccess(null, 'premium'));
    }

    #[Test]
    public function premium_content_requires_paid_tier(): void
    {
        $user = $this->user();

        $this->assertFalse($this->makeService('free')->userCanAccess($user, 'premium'));
        $this->assertFalse($this->makeService('none')->userCanAccess($user, 'premium'));
        $this->assertTrue($this->makeService('starter')->userCanAccess($user, 'premium'));
        $this->assertTrue($this->makeService('business')->userCanAccess($user, 'premium'));
        $this->assertTrue($this->makeService('starter')->userCanAccess($user, 'basic'));
        $this->assertTrue($this->makeService('business')->userCanAccess($user, 'institutional'));
    }

    #[Test]
    public function has_paid_subscription_reflects_tier(): void
    {
        $user = $this->user();

        $this->assertFalse($this->makeService('free')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('starter')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('business')->hasPaidSubscription($user));
    }

    #[Test]
    public function current_tier_is_none_without_user(): void
    {
        $this->assertSame('none', $this->makeService('free')->currentTier(null));
    }

    #[Test]
    public function premium_access_denied_when_monthly_allowance_exhausted(): void
    {
        $user = $this->user();

        $this->assertFalse($this->makeService('starter', viewsUsed: 300)->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function premium_access_granted_within_allowance(): void
    {
        $user = $this->user();

        $this->assertTrue($this->makeService('starter', viewsUsed: 299)->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function free_content_never_consumes_allowance(): void
    {
        $user = $this->user();

        $this->assertTrue($this->makeService('free', viewsUsed: 0)->userCanAccess($user, 'free'));
    }

    #[Test]
    public function unlimited_tier_never_blocked_by_views(): void
    {
        $user = $this->user();

        $service = $this->makeService('business', viewsUsed: 9999, tierLimits: ['views_per_month' => -1]);

        $this->assertSame(-1, $service->remainingPremiumViews($user));
        $this->assertTrue($service->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function remaining_views_is_allowance_minus_used(): void
    {
        $user = $this->user();

        $this->assertSame(100, $this->makeService('starter', viewsUsed: 200)->remainingPremiumViews($user));
        $this->assertSame(0, $this->makeService('starter', viewsUsed: 500)->remainingPremiumViews($user));
    }
}
