<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DepreciationMethodTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('straight_line', DepreciationMethod::STRAIGHT_LINE->value);
        $this->assertSame('reducing_balance', DepreciationMethod::REDUCING_BALANCE->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(DepreciationMethod::STRAIGHT_LINE, DepreciationMethod::from('straight_line'));
        $this->assertSame(DepreciationMethod::REDUCING_BALANCE, DepreciationMethod::from('reducing_balance'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        DepreciationMethod::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Straight Line', DepreciationMethod::STRAIGHT_LINE->label());
        $this->assertSame('Reducing Balance', DepreciationMethod::REDUCING_BALANCE->label());
    }
}
