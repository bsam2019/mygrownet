<?php

namespace Tests\Unit\GrowMart;

use App\Domain\GrowMart\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function from_kwacha_converts_to_ngwee(): void
    {
        $money = Money::fromKwacha(15.50);
        $this->assertEquals(1550, $money->ngwee());
    }

    #[Test]
    public function from_kwacha_rounds_correctly(): void
    {
        $money = Money::fromKwacha(10.999);
        $this->assertEquals(1100, $money->ngwee());
    }

    #[Test]
    public function from_ngwee_stores_as_is(): void
    {
        $money = Money::fromNgwee(1500);
        $this->assertEquals(1500, $money->ngwee());
    }

    #[Test]
    public function zero_creates_zero_ngwee(): void
    {
        $money = Money::zero();
        $this->assertEquals(0, $money->ngwee());
    }

    #[Test]
    public function to_kwacha_converts_correctly(): void
    {
        $money = Money::fromNgwee(1550);
        $this->assertEquals(15.50, $money->toKwacha());
    }

    #[Test]
    public function add_returns_new_instance_with_sum(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromKwacha(5.50);
        $result = $a->add($b);
        $this->assertEquals(1550, $result->ngwee());
        $this->assertNotSame($a, $result);
    }

    #[Test]
    public function subtract_returns_new_instance_with_difference(): void
    {
        $a = Money::fromKwacha(20.00);
        $b = Money::fromKwacha(8.00);
        $result = $a->subtract($b);
        $this->assertEquals(1200, $result->ngwee());
    }

    #[Test]
    public function multiply_returns_new_instance(): void
    {
        $money = Money::fromKwacha(5.00);
        $result = $money->multiply(3);
        $this->assertEquals(1500, $result->ngwee());
    }

    #[Test]
    public function format_returns_kwacha_string(): void
    {
        $money = Money::fromNgwee(1550);
        $this->assertEquals('K15.50', $money->format());
    }

    #[Test]
    public function format_handles_zero(): void
    {
        $this->assertEquals('K0.00', Money::zero()->format());
    }

    #[Test]
    public function equals_returns_true_for_same_value(): void
    {
        $this->assertTrue(Money::fromKwacha(10.00)->equals(Money::fromNgwee(1000)));
    }

    #[Test]
    public function equals_returns_false_for_different_values(): void
    {
        $this->assertFalse(Money::fromKwacha(10.00)->equals(Money::fromKwacha(20.00)));
    }
}
