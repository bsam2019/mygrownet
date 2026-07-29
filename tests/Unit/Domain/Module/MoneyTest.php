<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function fromAmount_creates_money()
    {
        $money = Money::fromAmount(1000, 'ZMW');

        $this->assertEquals(1000, $money->amount());
        $this->assertEquals('ZMW', $money->currency());
    }

    #[Test]
    public function fromAmount_defaults_to_zmw()
    {
        $money = Money::fromAmount(500);

        $this->assertEquals('ZMW', $money->currency());
    }

    #[Test]
    public function fromAmount_throws_on_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        Money::fromAmount(-1);
    }

    #[Test]
    public function zero_creates_zero_amount()
    {
        $money = Money::zero('USD');

        $this->assertEquals(0, $money->amount());
        $this->assertEquals('USD', $money->currency());
        $this->assertTrue($money->isZero());
    }

    #[Test]
    public function zero_defaults_to_zmw()
    {
        $money = Money::zero();

        $this->assertEquals('ZMW', $money->currency());
        $this->assertTrue($money->isZero());
    }

    #[Test]
    public function fromCents_converts_cents_to_whole()
    {
        $money = Money::fromCents(10000);

        $this->assertEquals(100, $money->amount());
    }

    #[Test]
    public function add_returns_new_money_with_sum()
    {
        $a = Money::fromAmount(100);
        $b = Money::fromAmount(250);

        $result = $a->add($b);

        $this->assertEquals(350, $result->amount());
        $this->assertNotSame($a, $result);
    }

    #[Test]
    public function add_throws_on_different_currency()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot operate on different currencies');

        $a = Money::fromAmount(100, 'ZMW');
        $b = Money::fromAmount(50, 'USD');

        $a->add($b);
    }

    #[Test]
    public function subtract_returns_new_money_with_difference()
    {
        $a = Money::fromAmount(250);
        $b = Money::fromAmount(100);

        $result = $a->subtract($b);

        $this->assertEquals(150, $result->amount());
    }

    #[Test]
    public function subtract_throws_on_different_currency()
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromAmount(100, 'ZMW')->subtract(Money::fromAmount(50, 'USD'));
    }

    #[Test]
    public function multiply_returns_new_money()
    {
        $money = Money::fromAmount(100);

        $result = $money->multiply(3);

        $this->assertEquals(300, $result->amount());
        $this->assertEquals('ZMW', $result->currency());
    }

    #[Test]
    public function isZero_returns_correctly()
    {
        $this->assertTrue(Money::fromAmount(0)->isZero());
        $this->assertFalse(Money::fromAmount(1)->isZero());
    }

    #[Test]
    public function isGreaterThan_compares_correctly()
    {
        $a = Money::fromAmount(200);
        $b = Money::fromAmount(100);
        $c = Money::fromAmount(200);

        $this->assertTrue($a->isGreaterThan($b));
        $this->assertFalse($b->isGreaterThan($a));
        $this->assertFalse($a->isGreaterThan($c));
    }

    #[Test]
    public function isGreaterThan_throws_on_different_currency()
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromAmount(100, 'ZMW')->isGreaterThan(Money::fromAmount(50, 'USD'));
    }

    #[Test]
    public function equals_compares_amount_and_currency()
    {
        $a = Money::fromAmount(100, 'ZMW');
        $b = Money::fromAmount(100, 'ZMW');
        $c = Money::fromAmount(100, 'USD');
        $d = Money::fromAmount(50, 'ZMW');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals($d));
    }

    #[Test]
    public function formatted_returns_kwacha_format()
    {
        $money = Money::fromAmount(1500);

        $this->assertEquals('K1,500.00', $money->formatted());
    }

    #[Test]
    public function formatted_with_zero()
    {
        $this->assertEquals('K0.00', Money::zero()->formatted());
    }

    #[Test]
    public function subtract_to_zero_works()
    {
        $a = Money::fromAmount(100);
        $b = Money::fromAmount(100);

        $result = $a->subtract($b);

        $this->assertTrue($result->isZero());
    }
}
