<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_from_ngwee_creates_correct_amount(): void
    {
        $money = Money::fromNgwee(1500);
        $this->assertEquals(1500, $money->getAmountInNgwee());
        $this->assertEquals(15.0, $money->getAmountInKwacha());
    }

    public function test_from_kwacha_creates_correct_amount(): void
    {
        $money = Money::fromKwacha(15.50);
        $this->assertEquals(1550, $money->getAmountInNgwee());
    }

    public function test_zero_returns_zero(): void
    {
        $money = Money::zero();
        $this->assertTrue($money->isZero());
        $this->assertEquals(0, $money->getAmountInNgwee());
    }

    public function test_add_two_amounts(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromKwacha(5.50);
        $result = $a->add($b);

        $this->assertEquals(1550, $result->getAmountInNgwee());
        $this->assertEquals(15.50, $result->getAmountInKwacha());
    }

    public function test_subtract_smaller_from_larger(): void
    {
        $a = Money::fromKwacha(20.00);
        $b = Money::fromKwacha(5.00);
        $result = $a->subtract($b);

        $this->assertEquals(1500, $result->getAmountInNgwee());
    }

    public function test_subtract_never_goes_below_zero(): void
    {
        $a = Money::fromKwacha(5.00);
        $b = Money::fromKwacha(20.00);
        $result = $a->subtract($b);

        $this->assertTrue($result->isZero());
    }

    public function test_multiply(): void
    {
        $money = Money::fromKwacha(10.50);
        $result = $money->multiply(3);

        $this->assertEquals(3150, $result->getAmountInNgwee());
    }

    public function test_percentage(): void
    {
        $money = Money::fromKwacha(200.00);
        $result = $money->percentage(15);

        $this->assertEquals(3000, $result->getAmountInNgwee());
        $this->assertEquals(30.00, $result->getAmountInKwacha());
    }

    public function test_comparisons(): void
    {
        $small = Money::fromKwacha(10.00);
        $large = Money::fromKwacha(100.00);
        $same = Money::fromKwacha(10.00);

        $this->assertTrue($small->isLessThan($large));
        $this->assertFalse($large->isLessThan($small));
        $this->assertTrue($large->isGreaterThan($small));
        $this->assertFalse($small->isGreaterThan($large));
        $this->assertTrue($small->equals($same));
        $this->assertFalse($small->equals($large));
    }

    public function test_format(): void
    {
        $money = Money::fromKwacha(1234.56);
        $this->assertEquals('K1,234.56', $money->format());
    }

    public function test_format_with_currency(): void
    {
        $money = Money::fromKwacha(99.99);
        $this->assertEquals('ZMW 99.99', $money->formatWithCurrency());
    }

    public function test_rounds_kwacha_to_nearest_ngwee(): void
    {
        $money = Money::fromKwacha(10.999);
        $this->assertEquals(1100, $money->getAmountInNgwee());
        $this->assertEquals(11.00, $money->getAmountInKwacha());
    }

    public function test_currency_is_zmw(): void
    {
        $this->assertEquals('ZMW', Money::zero()->getCurrency());
    }

    public function test_ngwee_arithmetic_precision(): void
    {
        $a = Money::fromNgwee(100);
        $b = Money::fromNgwee(33);
        $total = $a->add($b)->add($b)->add($b);
        $this->assertEquals(199, $total->getAmountInNgwee());
    }
}
