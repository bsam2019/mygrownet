<?php

namespace Tests\Unit\Domain\Wedding\ValueObjects;

use App\Domain\Wedding\ValueObjects\WeddingBudget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingBudgetTest extends TestCase
{
    public function test_from_amount(): void
    {
        $budget = WeddingBudget::fromAmount(50000);
        $this->assertEquals(50000, $budget->getAmount());
    }

    public function test_zero(): void
    {
        $budget = WeddingBudget::zero();
        $this->assertEquals(0, $budget->getAmount());
    }

    public function test_negative_amount_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WeddingBudget::fromAmount(-100);
    }

    public function test_add(): void
    {
        $a = WeddingBudget::fromAmount(30000);
        $b = WeddingBudget::fromAmount(20000);
        $result = $a->add($b);

        $this->assertEquals(50000, $result->getAmount());
    }

    public function test_subtract(): void
    {
        $a = WeddingBudget::fromAmount(50000);
        $b = WeddingBudget::fromAmount(20000);
        $result = $a->subtract($b);

        $this->assertEquals(30000, $result->getAmount());
    }

    public function test_subtract_negative_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $a = WeddingBudget::fromAmount(10000);
        $b = WeddingBudget::fromAmount(20000);
        $a->subtract($b);
    }

    public function test_allocate_percentage(): void
    {
        $budget = WeddingBudget::fromAmount(100000);
        $allocated = $budget->allocatePercentage(25);

        $this->assertEquals(25000, $allocated->getAmount());
    }

    public function test_allocate_percentage_out_of_bounds_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $budget = WeddingBudget::fromAmount(1000);
        $budget->allocatePercentage(101);
    }

    public function test_allocate_percentage_negative_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WeddingBudget::fromAmount(1000)->allocatePercentage(-1);
    }

    public function test_is_greater_than(): void
    {
        $a = WeddingBudget::fromAmount(100000);
        $b = WeddingBudget::fromAmount(50000);

        $this->assertTrue($a->isGreaterThan($b));
        $this->assertFalse($b->isGreaterThan($a));
    }

    public function test_is_less_than(): void
    {
        $a = WeddingBudget::fromAmount(100000);
        $b = WeddingBudget::fromAmount(50000);

        $this->assertTrue($b->isLessThan($a));
        $this->assertFalse($a->isLessThan($b));
    }

    public function test_equals(): void
    {
        $a = WeddingBudget::fromAmount(50000);
        $b = WeddingBudget::fromAmount(50000);
        $c = WeddingBudget::fromAmount(50001);

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_get_formatted_amount(): void
    {
        $budget = WeddingBudget::fromAmount(123456.78);
        $this->assertEquals('K123,456.78', $budget->getFormattedAmount());
    }

    public function test_to_array(): void
    {
        $budget = WeddingBudget::fromAmount(75000);
        $result = $budget->toArray();

        $this->assertEquals(75000, $result['amount']);
        $this->assertEquals('K75,000.00', $result['formatted']);
    }
}
