<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\CreatorTier;
use PHPUnit\Framework\TestCase;

class CreatorTierTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('bronze', CreatorTier::Bronze->value);
        $this->assertEquals('silver', CreatorTier::Silver->value);
        $this->assertEquals('gold', CreatorTier::Gold->value);
        $this->assertEquals('platinum', CreatorTier::Platinum->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Bronze', CreatorTier::Bronze->label());
        $this->assertEquals('Silver', CreatorTier::Silver->label());
        $this->assertEquals('Gold', CreatorTier::Gold->label());
        $this->assertEquals('Platinum', CreatorTier::Platinum->label());
    }

    public function test_color_returns_expected(): void
    {
        $this->assertEquals('#cd7f32', CreatorTier::Bronze->color());
        $this->assertEquals('#c0c0c0', CreatorTier::Silver->color());
        $this->assertEquals('#ffd700', CreatorTier::Gold->color());
        $this->assertEquals('#e5e4e2', CreatorTier::Platinum->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(CreatorTier::Gold, CreatorTier::fromString('gold'));
        $this->assertSame(CreatorTier::Gold, CreatorTier::fromString('GOLD'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CreatorTier::fromString('diamond');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(CreatorTier::Platinum, CreatorTier::tryFrom('platinum'));
        $this->assertNull(CreatorTier::tryFrom('ruby'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = CreatorTier::all();
        $this->assertCount(4, $all);
    }
}
