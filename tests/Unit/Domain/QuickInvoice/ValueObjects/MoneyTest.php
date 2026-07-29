<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class MoneyTest extends TestCase
{
    #[Test]
    public function create_with_valid_amount_and_currency(): void
    {
        $money = Money::create(100.50, 'USD');
        $this->assertSame(100.50, $money->amount());
        $this->assertSame('USD', $money->currency());
    }

    #[Test]
    public function create_uppercases_currency(): void
    {
        $money = Money::create(50, 'usd');
        $this->assertSame('USD', $money->currency());
    }

    #[Test]
    public function create_defaults_to_zmw(): void
    {
        $money = Money::create(10);
        $this->assertSame('ZMW', $money->currency());
    }

    #[Test]
    public function create_negative_amount_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');
        Money::create(-1);
    }

    #[Test]
    public function create_unsupported_currency_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported currency: JPY');
        Money::create(100, 'JPY');
    }

    #[Test]
    public function zero_returns_zero_amount(): void
    {
        $money = Money::zero('EUR');
        $this->assertSame(0.0, $money->amount());
        $this->assertSame('EUR', $money->currency());
    }

    #[Test]
    public function add_same_currency(): void
    {
        $a = Money::create(100, 'USD');
        $b = Money::create(50, 'USD');
        $result = $a->add($b);
        $this->assertSame(150.0, $result->amount());
        $this->assertSame('USD', $result->currency());
    }

    #[Test]
    public function add_different_currencies_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $a = Money::create(100, 'USD');
        $b = Money::create(50, 'ZMW');
        $a->add($b);
    }

    #[Test]
    public function subtract_same_currency(): void
    {
        $a = Money::create(100, 'ZMW');
        $b = Money::create(30, 'ZMW');
        $result = $a->subtract($b);
        $this->assertSame(70.0, $result->amount());
    }

    #[Test]
    public function subtract_clamps_to_zero(): void
    {
        $a = Money::create(20, 'ZMW');
        $b = Money::create(100, 'ZMW');
        $result = $a->subtract($b);
        $this->assertSame(0.0, $result->amount());
    }

    #[Test]
    public function subtract_different_currencies_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Money::create(100, 'GBP')->subtract(Money::create(10, 'EUR'));
    }

    #[Test]
    public function multiply(): void
    {
        $result = Money::create(10.50, 'USD')->multiply(3);
        $this->assertSame(31.5, $result->amount());
    }

    #[Test]
    public function percentage(): void
    {
        $result = Money::create(200, 'ZMW')->percentage(16);
        $this->assertSame(32.0, $result->amount());
    }

    #[Test]
    public function format_zmw(): void
    {
        $this->assertSame('K 1,234.56', Money::create(1234.56, 'ZMW')->format());
    }

    #[Test]
    public function format_usd(): void
    {
        $this->assertSame('$ 50.00', Money::create(50, 'USD')->format());
    }

    #[Test]
    public function format_eur(): void
    {
        $this->assertSame('€ 99.99', Money::create(99.99, 'EUR')->format());
    }

    #[Test]
    public function format_gbp(): void
    {
        $this->assertSame('£ 75.00', Money::create(75, 'GBP')->format());
    }

    #[Test]
    public function format_zar(): void
    {
        $this->assertSame('R 1,000.00', Money::create(1000, 'ZAR')->format());
    }

    #[Test]
    public function currency_symbol_zmw(): void
    {
        $this->assertSame('K', Money::create(1, 'ZMW')->currencySymbol());
    }

    #[Test]
    public function currency_symbol_usd(): void
    {
        $this->assertSame('$', Money::create(1, 'USD')->currencySymbol());
    }

    #[Test]
    public function toArray_returns_expected_structure(): void
    {
        $result = Money::create(99.99, 'ZMW')->toArray();
        $this->assertSame(['amount' => 99.99, 'currency' => 'ZMW', 'formatted' => 'K 99.99'], $result);
    }

    #[Test]
    public function add_does_not_mutate_original(): void
    {
        $a = Money::create(50, 'ZMW');
        $b = Money::create(25, 'ZMW');
        $a->add($b);
        $this->assertSame(50.0, $a->amount());
    }

    #[Test]
    public function supported_currencies_constant(): void
    {
        $this->assertSame(['ZMW', 'USD', 'EUR', 'GBP', 'ZAR'], Money::SUPPORTED_CURRENCIES);
    }
}
