<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\StarterKitTier;
use PHPUnit\Framework\TestCase;

class StarterKitTierTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('basic', StarterKitTier::Basic->value);
        $this->assertEquals('premium', StarterKitTier::Premium->value);
        $this->assertEquals('elite', StarterKitTier::Elite->value);
        $this->assertEquals('all', StarterKitTier::All->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Basic', StarterKitTier::Basic->label());
        $this->assertEquals('Premium', StarterKitTier::Premium->label());
        $this->assertEquals('Elite', StarterKitTier::Elite->label());
        $this->assertEquals('All Tiers', StarterKitTier::All->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', StarterKitTier::Basic->color());
        $this->assertEquals('#3b82f6', StarterKitTier::Premium->color());
        $this->assertEquals('#f59e0b', StarterKitTier::Elite->color());
        $this->assertEquals('#8b5cf6', StarterKitTier::All->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(StarterKitTier::Elite, StarterKitTier::fromString('elite'));
        $this->assertSame(StarterKitTier::Elite, StarterKitTier::fromString('ELITE'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StarterKitTier::fromString('ultimate');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(StarterKitTier::All, StarterKitTier::tryFrom('all'));
        $this->assertNull(StarterKitTier::tryFrom('none'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = StarterKitTier::all();
        $this->assertCount(4, $all);
    }
}
