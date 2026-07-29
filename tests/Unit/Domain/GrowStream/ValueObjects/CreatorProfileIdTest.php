<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\CreatorProfileId;
use PHPUnit\Framework\TestCase;

class CreatorProfileIdTest extends TestCase
{
    public function test_from_int_creates_with_positive_int(): void
    {
        $id = CreatorProfileId::fromInt(7);
        $this->assertEquals(7, $id->toInt());
    }

    public function test_from_int_rejects_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CreatorProfileId::fromInt(0);
    }

    public function test_from_int_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CreatorProfileId::fromInt(-3);
    }

    public function test_equality_same_value(): void
    {
        $a = CreatorProfileId::fromInt(15);
        $b = CreatorProfileId::fromInt(15);
        $this->assertTrue($a->equals($b));
    }

    public function test_equality_different_value(): void
    {
        $a = CreatorProfileId::fromInt(15);
        $b = CreatorProfileId::fromInt(25);
        $this->assertFalse($a->equals($b));
    }

    public function test_to_int_returns_original(): void
    {
        $id = CreatorProfileId::fromInt(100);
        $this->assertSame(100, $id->toInt());
    }
}
