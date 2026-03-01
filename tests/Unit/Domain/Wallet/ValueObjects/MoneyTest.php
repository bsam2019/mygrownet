<?php

namespace Tests\Unit\Domain\Wallet\ValueObjects;

use App\Domain\Wallet\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_can_create_money_from_kwacha()
    {
        $money = Money::fromKwacha(500);
        
        $this->assertEquals(500, $money->amount());
    }

    public function test_can_create_zero_money()
    {
        $money = Money::zero();
        
        $this->assertEquals(0, $money->amount());
        $this->assertTrue($money->isZero());
    }

    public function test_cannot_create_negative_money()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative');
        
        Money::fromKwacha(-100);
    }

    public function test_can_add_money()
    {
        $money1 = Money::fromKwacha(500);
        $money2 = Money::fromKwacha(300);
        
        $result = $money1->add($money2);
        
        $this->assertEquals(800, $result->amount());
    }

    public function test_can_subtract_money()
    {
        $money1 = Money::fromKwacha(500);
        $money2 = Money::fromKwacha(300);
        
        $result = $money1->subtract($money2);
        
        $this->assertEquals(200, $result->amount());
    }

    public function test_cannot_subtract_to_negative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subtraction would result in negative amount');
        
        $money1 = Money::fromKwacha(300);
        $money2 = Money::fromKwacha(500);
        
        $money1->subtract($money2);
    }

    public function test_can_multiply_money()
    {
        $money = Money::fromKwacha(100);
        
        $result = $money->multiply(2.5);
        
        $this->assertEquals(250, $result->amount());
    }

    public function test_cannot_multiply_by_negative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplication factor cannot be negative');
        
        $money = Money::fromKwacha(100);
        $money->multiply(-2);
    }

    public function test_can_divide_money()
    {
        $money = Money::fromKwacha(100);
        
        $result = $money->divide(2);
        
        $this->assertEquals(50, $result->amount());
    }

    public function test_cannot_divide_by_zero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero or negative number');
        
        $money = Money::fromKwacha(100);
        $money->divide(0);
    }

    public function test_can_calculate_percentage()
    {
        $money = Money::fromKwacha(1000);
        
        $result = $money->percentage(15);
        
        $this->assertEquals(150, $result->amount());
    }

    public function test_percentage_must_be_valid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Percentage must be between 0 and 100');
        
        $money = Money::fromKwacha(1000);
        $money->percentage(150);
    }

    public function test_can_compare_money()
    {
        $money1 = Money::fromKwacha(500);
        $money2 = Money::fromKwacha(300);
        $money3 = Money::fromKwacha(500);
        
        $this->assertTrue($money1->isGreaterThan($money2));
        $this->assertFalse($money2->isGreaterThan($money1));
        
        $this->assertTrue($money2->isLessThan($money1));
        $this->assertFalse($money1->isLessThan($money2));
        
        $this->assertTrue($money1->equals($money3));
        $this->assertFalse($money1->equals($money2));
    }

    public function test_can_check_if_positive()
    {
        $positive = Money::fromKwacha(100);
        $zero = Money::zero();
        
        $this->assertTrue($positive->isPositive());
        $this->assertFalse($zero->isPositive());
    }

    public function test_can_format_money()
    {
        $money = Money::fromKwacha(1234.56);
        
        $this->assertEquals('K1,234.56', $money->format());
        $this->assertEquals('1,234.56', $money->formatWithoutSymbol());
        $this->assertEquals('K1,234.56', (string) $money);
    }

    public function test_money_is_immutable()
    {
        $original = Money::fromKwacha(100);
        $modified = $original->add(Money::fromKwacha(50));
        
        $this->assertEquals(100, $original->amount());
        $this->assertEquals(150, $modified->amount());
    }

    public function test_can_convert_to_array()
    {
        $money = Money::fromKwacha(500);
        
        $array = $money->toArray();
        
        $this->assertEquals([
            'amount' => 500,
            'formatted' => 'K500.00',
            'currency' => 'ZMW',
        ], $array);
    }
}
