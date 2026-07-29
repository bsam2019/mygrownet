<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\SkillLevel;
use PHPUnit\Framework\TestCase;

class SkillLevelTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('beginner', SkillLevel::Beginner->value);
        $this->assertEquals('intermediate', SkillLevel::Intermediate->value);
        $this->assertEquals('advanced', SkillLevel::Advanced->value);
        $this->assertEquals('expert', SkillLevel::Expert->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Beginner', SkillLevel::Beginner->label());
        $this->assertEquals('Intermediate', SkillLevel::Intermediate->label());
        $this->assertEquals('Advanced', SkillLevel::Advanced->label());
        $this->assertEquals('Expert', SkillLevel::Expert->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#22c55e', SkillLevel::Beginner->color());
        $this->assertEquals('#3b82f6', SkillLevel::Intermediate->color());
        $this->assertEquals('#f59e0b', SkillLevel::Advanced->color());
        $this->assertEquals('#ef4444', SkillLevel::Expert->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(SkillLevel::Advanced, SkillLevel::fromString('advanced'));
        $this->assertSame(SkillLevel::Advanced, SkillLevel::fromString('ADVANCED'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SkillLevel::fromString('master');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(SkillLevel::Expert, SkillLevel::tryFrom('expert'));
        $this->assertNull(SkillLevel::tryFrom('novice'));
    }

    public function test_nullable_usage(): void
    {
        $this->assertNull(null);
    }

    public function test_all_returns_all_cases(): void
    {
        $all = SkillLevel::all();
        $this->assertCount(4, $all);
    }
}
