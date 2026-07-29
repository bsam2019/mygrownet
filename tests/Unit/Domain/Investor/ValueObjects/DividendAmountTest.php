<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\DividendAmount;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DividendAmountTest extends TestCase
{
    public function test_from_float_creates_valid(): void
    {
        $amount = DividendAmount::fromFloat(1000.50);
        $this->assertEquals(1000.50, $amount->value());
        $this->assertEquals('ZMW', $amount->currency());
    }

    public function test_throws_exception_for_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DividendAmount::fromFloat(-100);
    }

    public function test_zero_is_valid(): void
    {
        $amount = DividendAmount::fromFloat(0);
        $this->assertEquals(0, $amount->value());
    }

    public function test_custom_currency(): void
    {
        $amount = DividendAmount::fromFloat(500, 'USD');
        $this->assertEquals('USD', $amount->currency());
    }

    public function test_calculate_tax(): void
    {
        $amount = DividendAmount::fromFloat(1000);
        $tax = $amount->calculateTax(0.15);

        $this->assertEquals(150.0, $tax->value());
        $this->assertEquals('ZMW', $tax->currency());
    }

    public function test_after_tax(): void
    {
        $amount = DividendAmount::fromFloat(1000);
        $net = $amount->afterTax(0.15);

        $this->assertEquals(850.0, $net->value());
    }

    public function test_add(): void
    {
        $a = DividendAmount::fromFloat(500);
        $b = DividendAmount::fromFloat(300);
        $result = $a->add($b);

        $this->assertEquals(800.0, $result->value());
    }

    public function test_add_throws_for_different_currencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        DividendAmount::fromFloat(100, 'ZMW')->add(DividendAmount::fromFloat(100, 'USD'));
    }

    public function test_formatted(): void
    {
        $amount = DividendAmount::fromFloat(1500.50);
        $this->assertEquals('ZMW 1,500.50', $amount->formatted());
    }

    public function test_to_string(): void
    {
        $amount = DividendAmount::fromFloat(2500);
        $this->assertEquals('ZMW 2,500.00', (string) $amount);
    }
}
