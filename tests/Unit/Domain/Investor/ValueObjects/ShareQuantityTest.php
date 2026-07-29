<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\ShareQuantity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ShareQuantityTest extends TestCase
{
    public function test_from_int_creates_valid(): void
    {
        $qty = ShareQuantity::fromInt(100);
        $this->assertEquals(100, $qty->value());
    }

    public function test_throws_exception_for_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ShareQuantity::fromInt(-1);
    }

    public function test_zero_is_valid(): void
    {
        $qty = ShareQuantity::fromInt(0);
        $this->assertEquals(0, $qty->value());
    }

    public function test_add(): void
    {
        $a = ShareQuantity::fromInt(50);
        $b = ShareQuantity::fromInt(30);
        $result = $a->add($b);

        $this->assertEquals(80, $result->value());
        $this->assertNotSame($a, $result);
    }

    public function test_subtract(): void
    {
        $a = ShareQuantity::fromInt(50);
        $b = ShareQuantity::fromInt(20);
        $result = $a->subtract($b);

        $this->assertEquals(30, $result->value());
    }

    public function test_subtract_throws_when_insufficient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ShareQuantity::fromInt(10)->subtract(ShareQuantity::fromInt(20));
    }

    public function test_is_greater_than(): void
    {
        $this->assertTrue(ShareQuantity::fromInt(100)->isGreaterThan(ShareQuantity::fromInt(50)));
        $this->assertFalse(ShareQuantity::fromInt(50)->isGreaterThan(ShareQuantity::fromInt(50)));
        $this->assertFalse(ShareQuantity::fromInt(30)->isGreaterThan(ShareQuantity::fromInt(50)));
    }

    public function test_equality(): void
    {
        $a = ShareQuantity::fromInt(100);
        $b = ShareQuantity::fromInt(100);
        $c = ShareQuantity::fromInt(200);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_string(): void
    {
        $this->assertEquals('500', (string) ShareQuantity::fromInt(500));
    }
}
