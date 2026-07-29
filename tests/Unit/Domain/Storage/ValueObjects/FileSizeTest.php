<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\ValueObjects;

use App\Domain\Storage\ValueObjects\FileSize;
use PHPUnit\Framework\TestCase;

final class FileSizeTest extends TestCase
{
    public function test_can_create_from_bytes(): void
    {
        $size = FileSize::fromBytes(1024);
        $this->assertEquals(1024, $size->toBytes());
    }

    public function test_can_create_from_megabytes(): void
    {
        $size = FileSize::fromMegabytes(1);
        $this->assertEquals(1048576, $size->toBytes());
    }

    public function test_can_create_from_gigabytes(): void
    {
        $size = FileSize::fromGigabytes(1);
        $this->assertEquals(1073741824, $size->toBytes());
    }

    public function test_cannot_create_negative_size(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FileSize::fromBytes(-1);
    }

    public function test_zero_bytes_is_valid(): void
    {
        $size = FileSize::fromBytes(0);
        $this->assertEquals(0, $size->toBytes());
    }

    public function test_converts_to_megabytes_correctly(): void
    {
        $size = FileSize::fromBytes(5242880);
        $this->assertEquals(5.0, $size->toMegabytes());
    }

    public function test_converts_to_gigabytes_correctly(): void
    {
        $size = FileSize::fromBytes(2147483648);
        $this->assertEquals(2.0, $size->toGigabytes());
    }

    public function test_adds_two_sizes(): void
    {
        $a = FileSize::fromBytes(100);
        $b = FileSize::fromBytes(200);
        $result = $a->add($b);
        $this->assertEquals(300, $result->toBytes());
    }

    public function test_subtract_clamps_at_zero(): void
    {
        $a = FileSize::fromBytes(50);
        $b = FileSize::fromBytes(200);
        $result = $a->subtract($b);
        $this->assertEquals(0, $result->toBytes());
    }

    public function test_subtract_returns_difference(): void
    {
        $a = FileSize::fromBytes(200);
        $b = FileSize::fromBytes(50);
        $result = $a->subtract($b);
        $this->assertEquals(150, $result->toBytes());
    }

    public function test_is_greater_than(): void
    {
        $a = FileSize::fromBytes(200);
        $b = FileSize::fromBytes(100);
        $this->assertTrue($a->isGreaterThan($b));
        $this->assertFalse($b->isGreaterThan($a));
    }

    public function test_is_less_than(): void
    {
        $a = FileSize::fromBytes(100);
        $b = FileSize::fromBytes(200);
        $this->assertTrue($a->isLessThan($b));
        $this->assertFalse($b->isLessThan($a));
    }

    public function test_equals_checks_byte_equality(): void
    {
        $a = FileSize::fromBytes(100);
        $b = FileSize::fromBytes(100);
        $c = FileSize::fromBytes(101);
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_format_returns_bytes(): void
    {
        $size = FileSize::fromBytes(500);
        $this->assertEquals('500 B', $size->format());
    }

    public function test_format_returns_kilobytes(): void
    {
        $size = FileSize::fromBytes(2048);
        $this->assertEquals('2 KB', $size->format());
    }

    public function test_format_returns_megabytes(): void
    {
        $size = FileSize::fromBytes(5242880);
        $this->assertEquals('5 MB', $size->format());
    }

    public function test_format_returns_gigabytes(): void
    {
        $size = FileSize::fromBytes(3221225472);
        $this->assertEquals('3 GB', $size->format());
    }

    public function test_format_returns_terabytes(): void
    {
        $size = FileSize::fromBytes(1099511627776);
        $this->assertEquals('1 TB', $size->format());
    }

    public function test_add_is_immutable(): void
    {
        $a = FileSize::fromBytes(100);
        $b = FileSize::fromBytes(200);
        $a->add($b);
        $this->assertEquals(100, $a->toBytes());
    }

    public function test_from_megabytes_with_decimal(): void
    {
        $size = FileSize::fromMegabytes(1.5);
        $this->assertEquals(1572864, $size->toBytes());
    }

    public function test_from_gigabytes_with_decimal(): void
    {
        $size = FileSize::fromGigabytes(0.5);
        $this->assertEquals(536870912, $size->toBytes());
    }
}
