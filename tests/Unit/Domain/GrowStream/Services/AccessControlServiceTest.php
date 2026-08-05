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
    /**
     * @param int $secondsConsumed seconds of premium video consumed this month
     */
    private function makeService(
        string $tier,
        int $secondsConsumed = 0,
        array $tierLimits = ['watch_minutes_per_month' => 500],
    ): AccessControlService {
        $subscriptionService = $this->createMock(SubscriptionService::class);
        $subscriptionService->method('getUserTier')
            ->willReturn($tier);

        $viewRepo = $this->createMock(VideoViewRepositoryInterface::class);
        $viewRepo->method('sumPremiumWatchSecondsByUser')
            ->willReturn($secondsConsumed);

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
        $this->assertTrue($this->makeService('premium')->userCanAccess($user, 'premium'));
        $this->assertTrue($this->makeService('starter')->userCanAccess($user, 'basic'));
        $this->assertTrue($this->makeService('business')->userCanAccess($user, 'institutional'));
    }

    #[Test]
    public function has_paid_subscription_reflects_tier(): void
    {
        $user = $this->user();

        $this->assertFalse($this->makeService('free')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('starter')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('premium')->hasPaidSubscription($user));
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

        // 500 min allowance, 30,000 seconds consumed = 500 min → 0 remaining
        $this->assertFalse($this->makeService('starter', secondsConsumed: 30000)->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function premium_access_granted_within_allowance(): void
    {
        $user = $this->user();

        // 500 min allowance, 29,940 seconds = 499 min → 1 remaining
        $this->assertTrue($this->makeService('starter', secondsConsumed: 29940)->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function free_content_never_consumes_allowance(): void
    {
        $user = $this->user();

        $this->assertTrue($this->makeService('free', secondsConsumed: 0)->userCanAccess($user, 'free'));
    }

    #[Test]
    public function unlimited_tier_never_blocked(): void
    {
        $user = $this->user();

        $service = $this->makeService('business', secondsConsumed: 99999, tierLimits: ['watch_minutes_per_month' => -1]);

        $this->assertSame(-1, $service->remainingWatchMinutes($user));
        $this->assertTrue($service->userCanAccess($user, 'premium'));
    }

    #[Test]
    public function remaining_minutes_converts_seconds_to_minutes(): void
    {
        $user = $this->user();

        // 500 min allowance, 12,000 seconds = 200 min consumed → 300 remaining
        $this->assertSame(300, $this->makeService('starter', secondsConsumed: 12000)->remainingWatchMinutes($user));

        // 30,000 seconds = 500 min → 0 remaining (floor)
        $this->assertSame(0, $this->makeService('starter', secondsConsumed: 30000)->remainingWatchMinutes($user));

        // 30,059 seconds = 500.98 min → 0 remaining (floor)
        $this->assertSame(0, $this->makeService('starter', secondsConsumed: 30059)->remainingWatchMinutes($user));
    }
}
