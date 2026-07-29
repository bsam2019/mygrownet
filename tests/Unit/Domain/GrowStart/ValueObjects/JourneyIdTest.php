<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\ValueObjects;

use App\Domain\GrowStart\ValueObjects\JourneyId;
use PHPUnit\Framework\TestCase;

final class JourneyIdTest extends TestCase
{
    public function test_can_create_from_int(): void
    {
        $id = JourneyId::fromInt(42);
        $this->assertEquals(42, $id->toInt());
    }

    public function test_can_create_zero(): void
    {
        $id = JourneyId::fromInt(0);
        $this->assertEquals(0, $id->toInt());
    }

    public function test_cannot_create_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        JourneyId::fromInt(-1);
    }

    public function test_generate_returns_zero(): void
    {
        $id = JourneyId::generate();
        $this->assertEquals(0, $id->toInt());
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = JourneyId::fromInt(42);
        $b = JourneyId::fromInt(42);
        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $a = JourneyId::fromInt(42);
        $b = JourneyId::fromInt(99);
        $this->assertFalse($a->equals($b));
    }

    public function test_to_string_returns_stringified_int(): void
    {
        $id = JourneyId::fromInt(42);
        $this->assertEquals('42', (string) $id);
    }

    public function test_to_string_returns_zero_as_string(): void
    {
        $id = JourneyId::fromInt(0);
        $this->assertEquals('0', (string) $id);
    }
}
