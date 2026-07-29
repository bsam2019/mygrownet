<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\VideoId;
use PHPUnit\Framework\TestCase;

class VideoIdTest extends TestCase
{
    public function test_from_int_creates_with_positive_int(): void
    {
        $id = VideoId::fromInt(42);
        $this->assertEquals(42, $id->toInt());
    }

    public function test_from_int_rejects_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VideoId::fromInt(0);
    }

    public function test_from_int_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VideoId::fromInt(-5);
    }

    public function test_equality_same_value(): void
    {
        $a = VideoId::fromInt(10);
        $b = VideoId::fromInt(10);
        $this->assertTrue($a->equals($b));
    }

    public function test_equality_different_value(): void
    {
        $a = VideoId::fromInt(10);
        $b = VideoId::fromInt(20);
        $this->assertFalse($a->equals($b));
    }

    public function test_to_int_returns_original(): void
    {
        $id = VideoId::fromInt(99);
        $this->assertSame(99, $id->toInt());
    }
}
