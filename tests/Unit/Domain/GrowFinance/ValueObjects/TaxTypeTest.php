<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\TaxType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaxTypeTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('vat', TaxType::VAT->value);
        $this->assertSame('withholding', TaxType::WITHHOLDING->value);
        $this->assertSame('sales_tax', TaxType::SALES_TAX->value);
        $this->assertSame('other', TaxType::OTHER->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(TaxType::VAT, TaxType::from('vat'));
        $this->assertSame(TaxType::WITHHOLDING, TaxType::from('withholding'));
        $this->assertSame(TaxType::SALES_TAX, TaxType::from('sales_tax'));
        $this->assertSame(TaxType::OTHER, TaxType::from('other'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        TaxType::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('VAT', TaxType::VAT->label());
        $this->assertSame('Withholding Tax', TaxType::WITHHOLDING->label());
        $this->assertSame('Sales Tax', TaxType::SALES_TAX->label());
        $this->assertSame('Other', TaxType::OTHER->label());
    }
}
