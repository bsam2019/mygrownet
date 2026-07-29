<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function creates_from_kwacha(): void
    {
        $money = Money::fromKwacha(15.50);
        $this->assertEquals(1550, $money->amount());
        $this->assertEquals(15.50, $money->toKwacha());
    }

    #[Test]
    public function creates_from_ngwee(): void
    {
        $money = Money::fromNgwee(1550);
        $this->assertEquals(1550, $money->amount());
    }

    #[Test]
    public function creates_zero(): void
    {
        $money = Money::zero();
        $this->assertEquals(0, $money->amount());
    }

    #[Test]
    public function adds(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromKwacha(5.50);
        $result = $a->add($b);
        $this->assertEquals(1550, $result->amount());
    }

    #[Test]
    public function subtracts(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromKwacha(3.00);
        $result = $a->subtract($b);
        $this->assertEquals(700, $result->amount());
    }

    #[Test]
    public function subtract_clamps_to_zero(): void
    {
        $a = Money::fromKwacha(5.00);
        $b = Money::fromKwacha(10.00);
        $result = $a->subtract($b);
        $this->assertEquals(0, $result->amount());
    }

    #[Test]
    public function multiplies(): void
    {
        $money = Money::fromKwacha(10.50);
        $result = $money->multiply(3);
        $this->assertEquals(3150, $result->amount());
    }

    #[Test]
    public function formats(): void
    {
        $this->assertEquals('K15.50', Money::fromKwacha(15.50)->format());
        $this->assertEquals('K0.00', Money::zero()->format());
        $this->assertEquals('K100.00', Money::fromKwacha(100)->format());
    }

    #[Test]
    public function equals_checks_amount_and_currency(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromNgwee(1000);
        $this->assertTrue($a->equals($b));
    }

    #[Test]
    public function rejects_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::fromNgwee(-100);
    }

    #[Test]
    public function rounds_kwacha_correctly(): void
    {
        $this->assertEquals(1501, Money::fromKwacha(15.005)->amount());
        $this->assertEquals(1500, Money::fromKwacha(14.995)->amount());
    }
}
