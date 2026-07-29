<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\ValueObjects;

use App\Domain\GrowStart\ValueObjects\StageSlug;
use PHPUnit\Framework\TestCase;

final class StageSlugTest extends TestCase
{
    public function test_each_slug_has_named_constructor(): void
    {
        $this->assertEquals('idea', StageSlug::idea()->value());
        $this->assertEquals('validation', StageSlug::validation()->value());
        $this->assertEquals('planning', StageSlug::planning()->value());
        $this->assertEquals('registration', StageSlug::registration()->value());
        $this->assertEquals('launch', StageSlug::launch()->value());
        $this->assertEquals('accounting', StageSlug::accounting()->value());
        $this->assertEquals('marketing', StageSlug::marketing()->value());
        $this->assertEquals('growth', StageSlug::growth()->value());
    }

    public function test_can_create_from_string(): void
    {
        $slug = StageSlug::fromString('launch');
        $this->assertEquals('launch', $slug->value());
    }

    public function test_cannot_create_from_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StageSlug::fromString('invalid_slug');
    }

    public function test_all_returns_eight_slugs(): void
    {
        $all = StageSlug::all();
        $this->assertCount(8, $all);
        $this->assertContains('idea', $all);
        $this->assertContains('growth', $all);
    }

    public function test_order_returns_position_one_based(): void
    {
        $this->assertEquals(1, StageSlug::idea()->order());
        $this->assertEquals(2, StageSlug::validation()->order());
        $this->assertEquals(3, StageSlug::planning()->order());
        $this->assertEquals(4, StageSlug::registration()->order());
        $this->assertEquals(5, StageSlug::launch()->order());
        $this->assertEquals(6, StageSlug::accounting()->order());
        $this->assertEquals(7, StageSlug::marketing()->order());
        $this->assertEquals(8, StageSlug::growth()->order());
    }

    public function test_next_returns_next_slug_in_sequence(): void
    {
        $this->assertEquals('validation', StageSlug::idea()->next()->value());
        $this->assertEquals('planning', StageSlug::validation()->next()->value());
        $this->assertEquals('growth', StageSlug::marketing()->next()->value());
    }

    public function test_next_returns_null_for_last_slug(): void
    {
        $this->assertNull(StageSlug::growth()->next());
    }

    public function test_previous_returns_previous_slug(): void
    {
        $this->assertEquals('marketing', StageSlug::growth()->previous()->value());
        $this->assertEquals('launch', StageSlug::accounting()->previous()->value());
        $this->assertEquals('idea', StageSlug::validation()->previous()->value());
    }

    public function test_previous_returns_null_for_first_slug(): void
    {
        $this->assertNull(StageSlug::idea()->previous());
    }

    public function test_equals_returns_true_for_same_slug(): void
    {
        $this->assertTrue(StageSlug::idea()->equals(StageSlug::idea()));
        $this->assertFalse(StageSlug::idea()->equals(StageSlug::launch()));
    }

    public function test_to_string_returns_value(): void
    {
        $this->assertEquals('idea', (string) StageSlug::idea());
        $this->assertEquals('growth', (string) StageSlug::growth());
    }
}
