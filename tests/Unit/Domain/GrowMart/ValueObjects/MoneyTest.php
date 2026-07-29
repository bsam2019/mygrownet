<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\ValueObjects;

use App\Domain\GrowMart\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    #[Test]
    public function from_kwacha_converts_to_ngwee(): void
    {
        $money = Money::fromKwacha(15.50);
        $this->assertEquals(1550, $money->ngwee());
    }

    #[Test]
    public function from_kwacha_rounds_to_nearest_ngwee(): void
    {
        $money = Money::fromKwacha(10.999);
        $this->assertEquals(1100, $money->ngwee());
    }

    #[Test]
    public function from_kwacha_handles_zero(): void
    {
        $money = Money::fromKwacha(0.00);
        $this->assertEquals(0, $money->ngwee());
    }

    #[Test]
    public function from_ngwee_stores_exact_integer(): void
    {
        $money = Money::fromNgwee(1500);
        $this->assertEquals(1500, $money->ngwee());
    }

    #[Test]
    public function from_ngwee_handles_zero(): void
    {
        $money = Money::fromNgwee(0);
        $this->assertEquals(0, $money->ngwee());
    }

    #[Test]
    public function from_ngwee_handles_negative(): void
    {
        $money = Money::fromNgwee(-500);
        $this->assertEquals(-500, $money->ngwee());
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
    public function to_kwacha_handles_zero(): void
    {
        $this->assertEquals(0.0, Money::zero()->toKwacha());
    }

    #[Test]
    public function to_kwacha_handles_small_amounts(): void
    {
        $money = Money::fromNgwee(5);
        $this->assertEquals(0.05, $money->toKwacha());
    }

    #[Test]
    public function add_returns_new_instance_with_sum(): void
    {
        $a = Money::fromKwacha(10.00);
        $b = Money::fromKwacha(5.50);
        $result = $a->add($b);
        $this->assertEquals(1550, $result->ngwee());
    }

    #[Test]
    public function add_is_immutable(): void
    {
        $a = Money::fromNgwee(100);
        $b = Money::fromNgwee(200);
        $a->add($b);
        $this->assertEquals(100, $a->ngwee());
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
    public function subtract_can_return_negative(): void
    {
        $result = Money::fromNgwee(100)->subtract(Money::fromNgwee(500));
        $this->assertEquals(-400, $result->ngwee());
    }

    #[Test]
    public function subtract_is_immutable(): void
    {
        $a = Money::fromNgwee(500);
        $a->subtract(Money::fromNgwee(200));
        $this->assertEquals(500, $a->ngwee());
    }

    #[Test]
    public function multiply_returns_new_instance(): void
    {
        $money = Money::fromKwacha(5.00);
        $result = $money->multiply(3);
        $this->assertEquals(1500, $result->ngwee());
    }

    #[Test]
    public function multiply_by_zero(): void
    {
        $result = Money::fromKwacha(10.00)->multiply(0);
        $this->assertEquals(0, $result->ngwee());
    }

    #[Test]
    public function multiply_by_one_returns_same_value(): void
    {
        $result = Money::fromNgwee(777)->multiply(1);
        $this->assertEquals(777, $result->ngwee());
    }

    #[Test]
    public function multiply_is_immutable(): void
    {
        $money = Money::fromNgwee(100);
        $money->multiply(5);
        $this->assertEquals(100, $money->ngwee());
    }

    #[Test]
    public function format_returns_kwacha_string(): void
    {
        $this->assertEquals('K15.50', Money::fromNgwee(1550)->format());
    }

    #[Test]
    public function format_handles_zero(): void
    {
        $this->assertEquals('K0.00', Money::zero()->format());
    }

    #[Test]
    public function format_handles_small_values(): void
    {
        $this->assertEquals('K0.05', Money::fromNgwee(5)->format());
    }

    #[Test]
    public function format_handles_large_values(): void
    {
        $this->assertEquals('K1,234.56', Money::fromNgwee(123456)->format());
    }

    #[Test]
    public function equals_returns_true_for_same_ngwee(): void
    {
        $this->assertTrue(Money::fromKwacha(10.00)->equals(Money::fromNgwee(1000)));
    }

    #[Test]
    public function equals_returns_true_for_zero(): void
    {
        $this->assertTrue(Money::zero()->equals(Money::fromNgwee(0)));
    }

    #[Test]
    public function equals_returns_false_for_different_values(): void
    {
        $this->assertFalse(Money::fromKwacha(10.00)->equals(Money::fromKwacha(20.00)));
    }

    #[Test]
    public function chained_operations_work(): void
    {
        $result = Money::fromKwacha(10.00)
            ->add(Money::fromKwacha(5.00))
            ->subtract(Money::fromKwacha(3.00))
            ->multiply(2);
        $this->assertEquals(2400, $result->ngwee());
        $this->assertEquals('K24.00', $result->format());
    }

    #[Test]
    public function ngwee_value_persists_through_conversion(): void
    {
        $original = 12345;
        $money = Money::fromNgwee($original);
        $reconstructed = Money::fromKwacha($money->toKwacha());
        $this->assertEquals($original, $reconstructed->ngwee());
    }
}
