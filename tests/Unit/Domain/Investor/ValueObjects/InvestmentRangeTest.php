<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\InvestmentRange;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvestmentRangeTest extends TestCase
{
    public function test_from_creates_valid_range(): void
    {
        $range = InvestmentRange::from('25-50');
        $this->assertEquals('25-50', $range->value());
    }

    public function test_throws_exception_for_invalid_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InvestmentRange::from('invalid');
    }

    public function test_is_high_value(): void
    {
        $this->assertTrue(InvestmentRange::from('100-250')->isHighValue());
        $this->assertTrue(InvestmentRange::from('250+')->isHighValue());
        $this->assertFalse(InvestmentRange::from('25-50')->isHighValue());
        $this->assertFalse(InvestmentRange::from('50-100')->isHighValue());
    }

    public function test_get_minimum_amount(): void
    {
        $this->assertEquals(25000, InvestmentRange::from('25-50')->getMinimumAmount());
        $this->assertEquals(50000, InvestmentRange::from('50-100')->getMinimumAmount());
        $this->assertEquals(100000, InvestmentRange::from('100-250')->getMinimumAmount());
        $this->assertEquals(250000, InvestmentRange::from('250+')->getMinimumAmount());
    }

    public function test_get_maximum_amount(): void
    {
        $this->assertEquals(50000, InvestmentRange::from('25-50')->getMaximumAmount());
        $this->assertEquals(100000, InvestmentRange::from('50-100')->getMaximumAmount());
        $this->assertEquals(250000, InvestmentRange::from('100-250')->getMaximumAmount());
        $this->assertNull(InvestmentRange::from('250+')->getMaximumAmount());
    }

    public function test_get_display_name(): void
    {
        $this->assertEquals('K25,000 - K50,000', InvestmentRange::from('25-50')->getDisplayName());
        $this->assertEquals('K50,000 - K100,000', InvestmentRange::from('50-100')->getDisplayName());
        $this->assertEquals('K100,000 - K250,000', InvestmentRange::from('100-250')->getDisplayName());
        $this->assertEquals('K250,000+', InvestmentRange::from('250+')->getDisplayName());
    }

    public function test_equality(): void
    {
        $a = InvestmentRange::from('100-250');
        $b = InvestmentRange::from('100-250');
        $c = InvestmentRange::from('250+');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
