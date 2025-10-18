<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidSalaryException;
use App\Domain\Employee\ValueObjects\Salary;
use PHPUnit\Framework\TestCase;

class SalaryTest extends TestCase
{
    public function test_can_create_salary_from_kwacha(): void
    {
        $salary = Salary::fromKwacha(1000.50);
        
        $this->assertEquals(1000.50, $salary->getAmountInKwacha());
        $this->assertEquals(100050, $salary->getAmountInNgwee());
        $this->assertEquals('ZMW', $salary->getCurrency());
    }

    public function test_can_create_salary_from_ngwee(): void
    {
        $salary = Salary::fromNgwee(100050);
        
        $this->assertEquals(1000.50, $salary->getAmountInKwacha());
        $this->assertEquals(100050, $salary->getAmountInNgwee());
        $this->assertEquals('ZMW', $salary->getCurrency());
    }

    public function test_can_create_salary_with_custom_currency(): void
    {
        $salary = Salary::fromKwacha(1000.00, 'USD');
        
        $this->assertEquals('USD', $salary->getCurrency());
        $this->assertEquals(1000.00, $salary->getAmountInKwacha());
    }

    public function test_can_create_zero_salary(): void
    {
        $salary = Salary::zero();
        
        $this->assertEquals(0.00, $salary->getAmountInKwacha());
        $this->assertEquals(0, $salary->getAmountInNgwee());
        $this->assertTrue($salary->isZero());
    }

    public function test_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Salary amount cannot be negative');
        
        Salary::fromNgwee(-100);
    }

    public function test_throws_exception_for_empty_currency(): void
    {
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Currency cannot be empty');
        
        Salary::fromKwacha(1000.00, '');
    }

    public function test_throws_exception_for_invalid_currency_length(): void
    {
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Currency must be a 3-letter ISO code');
        
        Salary::fromKwacha(1000.00, 'US');
    }

    public function test_can_add_salaries(): void
    {
        $salary1 = Salary::fromKwacha(1000.00);
        $salary2 = Salary::fromKwacha(500.00);
        $result = $salary1->add($salary2);
        
        $this->assertEquals(1500.00, $result->getAmountInKwacha());
        $this->assertEquals('ZMW', $result->getCurrency());
    }

    public function test_can_subtract_salaries(): void
    {
        $salary1 = Salary::fromKwacha(1000.00);
        $salary2 = Salary::fromKwacha(300.00);
        $result = $salary1->subtract($salary2);
        
        $this->assertEquals(700.00, $result->getAmountInKwacha());
        $this->assertEquals('ZMW', $result->getCurrency());
    }

    public function test_throws_exception_when_subtraction_results_in_negative(): void
    {
        $salary1 = Salary::fromKwacha(500.00);
        $salary2 = Salary::fromKwacha(1000.00);
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Salary cannot be negative after subtraction');
        
        $salary1->subtract($salary2);
    }

    public function test_can_multiply_salary(): void
    {
        $salary = Salary::fromKwacha(1000.00);
        $result = $salary->multiply(1.5);
        
        $this->assertEquals(1500.00, $result->getAmountInKwacha());
    }

    public function test_throws_exception_for_negative_multiplier(): void
    {
        $salary = Salary::fromKwacha(1000.00);
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Salary multiplier cannot be negative');
        
        $salary->multiply(-1.5);
    }

    public function test_can_calculate_percentage(): void
    {
        $salary = Salary::fromKwacha(1000.00);
        $result = $salary->percentage(15.0);
        
        $this->assertEquals(150.00, $result->getAmountInKwacha());
    }

    public function test_throws_exception_for_invalid_percentage(): void
    {
        $salary = Salary::fromKwacha(1000.00);
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Percentage must be between 0 and 100');
        
        $salary->percentage(150.0);
    }

    public function test_throws_exception_for_negative_percentage(): void
    {
        $salary = Salary::fromKwacha(1000.00);
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Percentage must be between 0 and 100');
        
        $salary->percentage(-10.0);
    }

    public function test_comparison_methods(): void
    {
        $salary1 = Salary::fromKwacha(1000.00);
        $salary2 = Salary::fromKwacha(500.00);
        $salary3 = Salary::fromKwacha(1000.00);
        
        $this->assertTrue($salary1->isGreaterThan($salary2));
        $this->assertFalse($salary2->isGreaterThan($salary1));
        $this->assertTrue($salary2->isLessThan($salary1));
        $this->assertFalse($salary1->isLessThan($salary2));
        $this->assertTrue($salary1->equals($salary3));
        $this->assertFalse($salary1->equals($salary2));
    }

    public function test_throws_exception_for_different_currency_operations(): void
    {
        $salaryZMW = Salary::fromKwacha(1000.00, 'ZMW');
        $salaryUSD = Salary::fromKwacha(1000.00, 'USD');
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Cannot perform operation on different currencies: ZMW and USD');
        
        $salaryZMW->add($salaryUSD);
    }

    public function test_throws_exception_for_different_currency_comparison(): void
    {
        $salaryZMW = Salary::fromKwacha(1000.00, 'ZMW');
        $salaryUSD = Salary::fromKwacha(1000.00, 'USD');
        
        $this->expectException(InvalidSalaryException::class);
        $this->expectExceptionMessage('Cannot perform operation on different currencies: ZMW and USD');
        
        $salaryZMW->isGreaterThan($salaryUSD);
    }

    public function test_formatting(): void
    {
        $salary = Salary::fromKwacha(1234.56);
        
        $this->assertEquals('ZMW 1234.56', $salary->format());
        $this->assertEquals('ZMW 1234.56', $salary->toString());
        $this->assertEquals('ZMW 1234.56', (string) $salary);
    }

    public function test_zero_salary_detection(): void
    {
        $zeroSalary = Salary::zero();
        $nonZeroSalary = Salary::fromKwacha(100.00);
        
        $this->assertTrue($zeroSalary->isZero());
        $this->assertFalse($nonZeroSalary->isZero());
    }

    public function test_rounding_precision(): void
    {
        // Test that fractional ngwee are properly rounded
        $salary = Salary::fromKwacha(10.555); // Should round to 10.56
        
        $this->assertEquals(10.56, $salary->getAmountInKwacha());
        $this->assertEquals(1056, $salary->getAmountInNgwee());
    }

    public function test_equality_with_different_currencies(): void
    {
        $salaryZMW = Salary::fromKwacha(1000.00, 'ZMW');
        $salaryUSD = Salary::fromKwacha(1000.00, 'USD');
        
        $this->assertFalse($salaryZMW->equals($salaryUSD));
    }
}