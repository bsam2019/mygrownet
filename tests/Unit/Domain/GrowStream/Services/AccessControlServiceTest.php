<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStream\Services;

use App\Domain\GrowStream\Services\AccessControlService;
use App\Domain\Module\Services\SubscriptionService;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccessControlServiceTest extends TestCase
{
    private function makeService(string $tier): AccessControlService
    {
        $subscriptionService = $this->createMock(SubscriptionService::class);
        $subscriptionService->method('getUserTier')
            ->willReturn($tier);

        return new AccessControlService($subscriptionService);
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
        $user = new User(['id' => 1]);

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
        $user = new User(['id' => 1]);

        $this->assertFalse($this->makeService('free')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('starter')->hasPaidSubscription($user));
        $this->assertTrue($this->makeService('business')->hasPaidSubscription($user));
    }

    #[Test]
    public function current_tier_is_none_without_user(): void
    {
        $this->assertSame('none', $this->makeService('free')->currentTier(null));
    }
}
