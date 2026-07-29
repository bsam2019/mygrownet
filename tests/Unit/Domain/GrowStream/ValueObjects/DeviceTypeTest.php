<?php

namespace Tests\Unit\Domain\GrowStream\ValueObjects;

use App\Domain\GrowStream\ValueObjects\DeviceType;
use PHPUnit\Framework\TestCase;

class DeviceTypeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertEquals('mobile', DeviceType::Mobile->value);
        $this->assertEquals('tablet', DeviceType::Tablet->value);
        $this->assertEquals('desktop', DeviceType::Desktop->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertEquals('Mobile', DeviceType::Mobile->label());
        $this->assertEquals('Tablet', DeviceType::Tablet->label());
        $this->assertEquals('Desktop', DeviceType::Desktop->label());
    }

    public function test_color_returns_null(): void
    {
        $this->assertNull(DeviceType::Mobile->color());
        $this->assertNull(DeviceType::Tablet->color());
        $this->assertNull(DeviceType::Desktop->color());
    }

    public function test_from_string_case_insensitive(): void
    {
        $this->assertSame(DeviceType::Desktop, DeviceType::fromString('desktop'));
        $this->assertSame(DeviceType::Desktop, DeviceType::fromString('DESKTOP'));
    }

    public function test_from_string_throws_for_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DeviceType::fromString('watch');
    }

    public function test_try_from_works(): void
    {
        $this->assertSame(DeviceType::Tablet, DeviceType::tryFrom('tablet'));
        $this->assertNull(DeviceType::tryFrom('tv'));
    }

    public function test_all_returns_all_cases(): void
    {
        $all = DeviceType::all();
        $this->assertCount(3, $all);
    }
}
