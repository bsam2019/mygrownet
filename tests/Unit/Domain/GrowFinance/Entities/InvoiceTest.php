<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Invoice;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    private Invoice $invoice;

    protected function setUp(): void
    {
        $this->invoice = new Invoice(
            id: 1, businessId: 5, customerId: 10, templateId: null,
            invoiceNumber: 'INV-001', invoiceDate: new DateTimeImmutable('2026-01-01'),
            dueDate: new DateTimeImmutable('2026-01-31'), status: InvoiceStatus::SENT,
            subtotal: 1000.0, taxAmount: 160.0, discountAmount: 0.0,
            totalAmount: 1160.0, amountPaid: 0.0, notes: null, terms: null,
            createdAt: null, updatedAt: null,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->invoice->id);
        $this->assertSame('INV-001', $this->invoice->invoiceNumber);
        $this->assertSame(InvoiceStatus::SENT, $this->invoice->status);
    }

    #[Test]
    public function get_balance_due_returns_difference()
    {
        $this->assertSame(1160.0, $this->invoice->getBalanceDue());
    }

    #[Test]
    public function is_paid_returns_false_for_sent()
    {
        $this->assertFalse($this->invoice->isPaid());
    }

    #[Test]
    public function is_paid_returns_true()
    {
        $paid = new Invoice(
            id: 2, businessId: 5, customerId: 10, templateId: null,
            invoiceNumber: 'INV-002', invoiceDate: new DateTimeImmutable('2026-01-01'),
            dueDate: null, status: InvoiceStatus::PAID,
            subtotal: 500.0, taxAmount: 0.0, discountAmount: 0.0,
            totalAmount: 500.0, amountPaid: 500.0, notes: null, terms: null,
            createdAt: null, updatedAt: null,
        );
        $this->assertTrue($paid->isPaid());
    }

    #[Test]
    public function is_overdue_returns_false_when_no_due_date()
    {
        $inv = new Invoice(
            id: 3, businessId: 5, customerId: null, templateId: null,
            invoiceNumber: null, invoiceDate: null, dueDate: null,
            status: InvoiceStatus::SENT, subtotal: 0.0, taxAmount: 0.0,
            discountAmount: 0.0, totalAmount: 100.0, amountPaid: 0.0,
            notes: null, terms: null, createdAt: null, updatedAt: null,
        );
        $this->assertFalse($inv->isOverdue());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $invoice = Invoice::reconstitute([
            'id' => 1, 'business_id' => 5, 'customer_id' => 10,
            'invoice_number' => 'INV-001', 'status' => 'paid',
            'total_amount' => 1000.0, 'amount_paid' => 1000.0,
        ]);

        $this->assertSame(InvoiceStatus::PAID, $invoice->status);
        $this->assertSame(1000.0, $invoice->totalAmount);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->invoice->toArray();

        $this->assertSame('INV-001', $array['invoice_number']);
        $this->assertSame('sent', $array['status']);
        $this->assertSame(1160.0, $array['balance_due']);
    }
}
