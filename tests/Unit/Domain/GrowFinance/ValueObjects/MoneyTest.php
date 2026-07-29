<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function from_kwacha_creates_correct_ngwee_amount()
    {
        $money = Money::fromKwacha(100.50);
        $this->assertSame(10050, $money->amount());
    }

    #[Test]
    public function from_ngwee_creates_money_with_correct_amount()
    {
        $money = Money::fromNgwee(10050);
        $this->assertSame(10050, $money->amount());
    }

    #[Test]
    public function zero_returns_zero_amount()
    {
        $money = Money::zero();
        $this->assertSame(0, $money->amount());
        $this->assertTrue($money->isZero());
    }

    #[Test]
    public function to_kwacha_converts_correctly()
    {
        $money = Money::fromNgwee(10050);
        $this->assertSame(100.50, $money->toKwacha());
    }

    #[Test]
    public function format_returns_correct_string()
    {
        $money = Money::fromKwacha(1500.00);
        $this->assertSame('K1,500.00', $money->format());
    }

    #[Test]
    public function currency_returns_zmw_by_default()
    {
        $money = Money::zero();
        $this->assertSame('ZMW', $money->currency());
    }

    #[Test]
    public function add_returns_new_instance_with_sum()
    {
        $a = Money::fromKwacha(100.00);
        $b = Money::fromKwacha(50.50);
        $result = $a->add($b);
        $this->assertSame(15050, $result->amount());
        $this->assertNotSame($a, $result);
    }

    #[Test]
    public function subtract_returns_new_instance_with_difference()
    {
        $a = Money::fromKwacha(100.00);
        $b = Money::fromKwacha(30.00);
        $result = $a->subtract($b);
        $this->assertSame(7000, $result->amount());
    }

    #[Test]
    public function multiply_returns_new_instance()
    {
        $money = Money::fromKwacha(100.00);
        $result = $money->multiply(1.5);
        $this->assertSame(15000, $result->amount());
    }

    #[Test]
    public function percentage_returns_correct_amount()
    {
        $money = Money::fromKwacha(200.00);
        $result = $money->percentage(15);
        $this->assertSame(3000, $result->amount());
    }

    #[Test]
    public function is_positive_negative_zero_work_correctly()
    {
        $positive = Money::fromKwacha(10.00);
        $negative = Money::fromKwacha(-5.00);
        $zero = Money::zero();

        $this->assertTrue($positive->isPositive());
        $this->assertFalse($positive->isNegative());
        $this->assertFalse($positive->isZero());

        $this->assertFalse($negative->isPositive());
        $this->assertTrue($negative->isNegative());
        $this->assertFalse($negative->isZero());

        $this->assertFalse($zero->isPositive());
        $this->assertFalse($zero->isNegative());
        $this->assertTrue($zero->isZero());
    }

    #[Test]
    public function equals_returns_true_for_same_amount_and_currency()
    {
        $a = Money::fromKwacha(100.00);
        $b = Money::fromKwacha(100.00);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function equals_returns_false_for_different_amount()
    {
        $a = Money::fromKwacha(100.00);
        $b = Money::fromKwacha(50.00);
        $this->assertFalse($a->equals($b));
    }

    #[Test]
    public function greater_than_and_less_than_work()
    {
        $big = Money::fromKwacha(100.00);
        $small = Money::fromKwacha(50.00);

        $this->assertTrue($big->greaterThan($small));
        $this->assertFalse($small->greaterThan($big));
        $this->assertTrue($small->lessThan($big));
        $this->assertFalse($big->lessThan($small));
    }

    #[Test]
    public function from_kwacha_with_string_works()
    {
        $money = Money::fromKwacha('99.99');
        $this->assertSame(9999, $money->amount());
    }

    #[Test]
    public function subtract_can_produce_negative_amount()
    {
        $a = Money::fromKwacha(30.00);
        $b = Money::fromKwacha(100.00);
        $result = $a->subtract($b);
        $this->assertTrue($result->isNegative());
        $this->assertSame(-7000, $result->amount());
    }

    #[Test]
    public function multiply_with_zero_returns_zero()
    {
        $money = Money::fromKwacha(100.00);
        $result = $money->multiply(0);
        $this->assertTrue($result->isZero());
    }

    #[Test]
    public function multiply_with_negative_produces_negative()
    {
        $money = Money::fromKwacha(100.00);
        $result = $money->multiply(-1);
        $this->assertTrue($result->isNegative());
    }
}
