<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('cash', PaymentMethod::CASH->value);
        $this->assertSame('bank', PaymentMethod::BANK->value);
        $this->assertSame('mobile_money', PaymentMethod::MOBILE_MONEY->value);
        $this->assertSame('cheque', PaymentMethod::CHEQUE->value);
        $this->assertSame('credit', PaymentMethod::CREDIT->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(PaymentMethod::CASH, PaymentMethod::from('cash'));
        $this->assertSame(PaymentMethod::BANK, PaymentMethod::from('bank'));
        $this->assertSame(PaymentMethod::MOBILE_MONEY, PaymentMethod::from('mobile_money'));
        $this->assertSame(PaymentMethod::CHEQUE, PaymentMethod::from('cheque'));
        $this->assertSame(PaymentMethod::CREDIT, PaymentMethod::from('credit'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        PaymentMethod::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Cash', PaymentMethod::CASH->label());
        $this->assertSame('Bank Transfer', PaymentMethod::BANK->label());
        $this->assertSame('Mobile Money', PaymentMethod::MOBILE_MONEY->label());
        $this->assertSame('Cheque', PaymentMethod::CHEQUE->label());
        $this->assertSame('On Credit', PaymentMethod::CREDIT->label());
    }

    #[Test]
    public function icon_returns_correct_string()
    {
        $this->assertSame('banknotes', PaymentMethod::CASH->icon());
        $this->assertSame('building-library', PaymentMethod::BANK->icon());
        $this->assertSame('device-phone-mobile', PaymentMethod::MOBILE_MONEY->icon());
        $this->assertSame('document-text', PaymentMethod::CHEQUE->icon());
        $this->assertSame('credit-card', PaymentMethod::CREDIT->icon());
    }
}
