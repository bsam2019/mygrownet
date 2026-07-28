<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\SitePlan;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SitePlanTest extends TestCase
{
    public function test_starter_plan(): void
    {
        $plan = SitePlan::starter();
        $this->assertTrue($plan->isStarter());
        $this->assertFalse($plan->isBusiness());
        $this->assertFalse($plan->isPro());
        $this->assertEquals('starter', $plan->value());
    }

    public function test_business_plan(): void
    {
        $plan = SitePlan::business();
        $this->assertTrue($plan->isBusiness());
    }

    public function test_pro_plan(): void
    {
        $plan = SitePlan::pro();
        $this->assertTrue($plan->isPro());
    }

    public function test_from_string(): void
    {
        $this->assertTrue(SitePlan::fromString('pro')->isPro());
        $this->assertTrue(SitePlan::fromString('starter')->isStarter());
        $this->assertTrue(SitePlan::fromString('business')->isBusiness());
    }

    public function test_from_string_invalid_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SitePlan::fromString('enterprise');
    }

    public function test_starter_limits(): void
    {
        $limits = SitePlan::starter()->getLimits();
        $this->assertEquals(5, $limits['pages']);
        $this->assertEquals(10, $limits['products']);
        $this->assertEquals(100, $limits['storage_mb']);
        $this->assertFalse($limits['custom_domain']);
        $this->assertFalse($limits['remove_branding']);
        $this->assertFalse($limits['analytics']);
    }

    public function test_starter_feature_methods(): void
    {
        $plan = SitePlan::starter();
        $this->assertEquals(5, $plan->getPageLimit());
        $this->assertEquals(10, $plan->getProductLimit());
        $this->assertEquals(100, $plan->getStorageLimitMb());
        $this->assertFalse($plan->canUseCustomDomain());
        $this->assertFalse($plan->canRemoveBranding());
        $this->assertFalse($plan->hasAnalytics());
    }

    public function test_business_limits(): void
    {
        $plan = SitePlan::business();
        $this->assertEquals(20, $plan->getPageLimit());
        $this->assertEquals(50, $plan->getProductLimit());
        $this->assertEquals(500, $plan->getStorageLimitMb());
        $this->assertTrue($plan->canUseCustomDomain());
        $this->assertTrue($plan->canRemoveBranding());
        $this->assertTrue($plan->hasAnalytics());
    }

    public function test_pro_limits(): void
    {
        $plan = SitePlan::pro();
        $this->assertEquals(-1, $plan->getPageLimit());
        $this->assertEquals(-1, $plan->getProductLimit());
        $this->assertEquals(2000, $plan->getStorageLimitMb());
        $this->assertTrue($plan->canUseCustomDomain());
        $this->assertTrue($plan->canRemoveBranding());
        $this->assertTrue($plan->hasAnalytics());
    }

    public function test_equals(): void
    {
        $this->assertTrue(SitePlan::starter()->equals(SitePlan::starter()));
        $this->assertFalse(SitePlan::starter()->equals(SitePlan::pro()));
    }

    public function test_get_limits_returns_array(): void
    {
        $limits = SitePlan::starter()->getLimits();
        $this->assertIsArray($limits);
        $this->assertArrayHasKey('pages', $limits);
        $this->assertArrayHasKey('products', $limits);
        $this->assertArrayHasKey('storage_mb', $limits);
        $this->assertArrayHasKey('custom_domain', $limits);
        $this->assertArrayHasKey('remove_branding', $limits);
        $this->assertArrayHasKey('analytics', $limits);
    }
}
