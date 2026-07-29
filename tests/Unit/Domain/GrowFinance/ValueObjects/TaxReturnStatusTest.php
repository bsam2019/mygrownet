<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\TaxReturnStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaxReturnStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('draft', TaxReturnStatus::DRAFT->value);
        $this->assertSame('filed', TaxReturnStatus::FILED->value);
        $this->assertSame('submitted', TaxReturnStatus::SUBMITTED->value);
        $this->assertSame('paid', TaxReturnStatus::PAID->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(TaxReturnStatus::DRAFT, TaxReturnStatus::from('draft'));
        $this->assertSame(TaxReturnStatus::FILED, TaxReturnStatus::from('filed'));
        $this->assertSame(TaxReturnStatus::SUBMITTED, TaxReturnStatus::from('submitted'));
        $this->assertSame(TaxReturnStatus::PAID, TaxReturnStatus::from('paid'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        TaxReturnStatus::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Draft', TaxReturnStatus::DRAFT->label());
        $this->assertSame('Filed', TaxReturnStatus::FILED->label());
        $this->assertSame('Submitted', TaxReturnStatus::SUBMITTED->label());
        $this->assertSame('Paid', TaxReturnStatus::PAID->label());
    }
}
