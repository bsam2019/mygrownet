<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\AccessLevel;
use PHPUnit\Framework\TestCase;

class AccessLevelTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('free', AccessLevel::Free->value);
        $this->assertEquals('basic', AccessLevel::Basic->value);
        $this->assertEquals('premium', AccessLevel::Premium->value);
        $this->assertEquals('institutional', AccessLevel::Institutional->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Free', AccessLevel::Free->label());
        $this->assertEquals('Basic', AccessLevel::Basic->label());
        $this->assertEquals('Premium', AccessLevel::Premium->label());
        $this->assertEquals('Institutional', AccessLevel::Institutional->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', AccessLevel::Free->color());
        $this->assertEquals('#3b82f6', AccessLevel::Basic->color());
        $this->assertEquals('#f59e0b', AccessLevel::Premium->color());
        $this->assertEquals('#8b5cf6', AccessLevel::Institutional->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(AccessLevel::Premium, AccessLevel::fromString('premium'));
        $this->assertSame(AccessLevel::Premium, AccessLevel::fromString('PREMIUM'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AccessLevel::fromString('enterprise');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(AccessLevel::Basic, AccessLevel::tryFrom('basic'));
        $this->assertNull(AccessLevel::tryFrom('ultra'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = AccessLevel::all();
        $this->assertCount(4, $all);
        $this->assertEquals('free', $all[0]['value']);
        $this->assertEquals('Free', $all[0]['label']);
    }
}
