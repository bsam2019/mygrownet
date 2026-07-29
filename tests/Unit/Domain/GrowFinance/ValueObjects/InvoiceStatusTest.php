<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('draft', InvoiceStatus::DRAFT->value);
        $this->assertSame('sent', InvoiceStatus::SENT->value);
        $this->assertSame('paid', InvoiceStatus::PAID->value);
        $this->assertSame('partial', InvoiceStatus::PARTIAL->value);
        $this->assertSame('overdue', InvoiceStatus::OVERDUE->value);
        $this->assertSame('cancelled', InvoiceStatus::CANCELLED->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(InvoiceStatus::DRAFT, InvoiceStatus::from('draft'));
        $this->assertSame(InvoiceStatus::SENT, InvoiceStatus::from('sent'));
        $this->assertSame(InvoiceStatus::PAID, InvoiceStatus::from('paid'));
        $this->assertSame(InvoiceStatus::PARTIAL, InvoiceStatus::from('partial'));
        $this->assertSame(InvoiceStatus::OVERDUE, InvoiceStatus::from('overdue'));
        $this->assertSame(InvoiceStatus::CANCELLED, InvoiceStatus::from('cancelled'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        InvoiceStatus::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Draft', InvoiceStatus::DRAFT->label());
        $this->assertSame('Sent', InvoiceStatus::SENT->label());
        $this->assertSame('Paid', InvoiceStatus::PAID->label());
        $this->assertSame('Partial', InvoiceStatus::PARTIAL->label());
        $this->assertSame('Overdue', InvoiceStatus::OVERDUE->label());
        $this->assertSame('Cancelled', InvoiceStatus::CANCELLED->label());
    }

    #[Test]
    public function color_returns_correct_string()
    {
        $this->assertSame('gray', InvoiceStatus::DRAFT->color());
        $this->assertSame('blue', InvoiceStatus::SENT->color());
        $this->assertSame('emerald', InvoiceStatus::PAID->color());
        $this->assertSame('amber', InvoiceStatus::PARTIAL->color());
        $this->assertSame('red', InvoiceStatus::OVERDUE->color());
        $this->assertSame('gray', InvoiceStatus::CANCELLED->color());
    }
}
