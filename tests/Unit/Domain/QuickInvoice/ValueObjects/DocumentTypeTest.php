<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DocumentTypeTest extends TestCase
{
    #[Test]
    public function cases_have_expected_values(): void
    {
        $this->assertSame('invoice', DocumentType::INVOICE->value);
        $this->assertSame('delivery_note', DocumentType::DELIVERY_NOTE->value);
        $this->assertSame('quotation', DocumentType::QUOTATION->value);
        $this->assertSame('receipt', DocumentType::RECEIPT->value);
    }

    #[Test]
    public function label_returns_capitalized_name(): void
    {
        $this->assertSame('Invoice', DocumentType::INVOICE->label());
        $this->assertSame('Delivery Note', DocumentType::DELIVERY_NOTE->label());
        $this->assertSame('Quotation', DocumentType::QUOTATION->label());
        $this->assertSame('Receipt', DocumentType::RECEIPT->label());
    }

    #[Test]
    public function prefix_returns_expected_code(): void
    {
        $this->assertSame('INV', DocumentType::INVOICE->prefix());
        $this->assertSame('DN', DocumentType::DELIVERY_NOTE->prefix());
        $this->assertSame('QT', DocumentType::QUOTATION->prefix());
        $this->assertSame('RCP', DocumentType::RECEIPT->prefix());
    }

    #[Test]
    public function show_due_date_true_for_invoice_and_quotation(): void
    {
        $this->assertTrue(DocumentType::INVOICE->showDueDate());
        $this->assertTrue(DocumentType::QUOTATION->showDueDate());
    }

    #[Test]
    public function show_due_date_false_for_delivery_note_and_receipt(): void
    {
        $this->assertFalse(DocumentType::DELIVERY_NOTE->showDueDate());
        $this->assertFalse(DocumentType::RECEIPT->showDueDate());
    }

    #[Test]
    public function show_payment_status_true_for_invoice_and_receipt(): void
    {
        $this->assertTrue(DocumentType::INVOICE->showPaymentStatus());
        $this->assertTrue(DocumentType::RECEIPT->showPaymentStatus());
    }

    #[Test]
    public function show_payment_status_false_for_delivery_note_and_quotation(): void
    {
        $this->assertFalse(DocumentType::DELIVERY_NOTE->showPaymentStatus());
        $this->assertFalse(DocumentType::QUOTATION->showPaymentStatus());
    }

    #[Test]
    public function from_valid_string_returns_case(): void
    {
        $this->assertEquals(DocumentType::INVOICE, DocumentType::from('invoice'));
        $this->assertEquals(DocumentType::DELIVERY_NOTE, DocumentType::from('delivery_note'));
    }

    #[Test]
    public function try_from_valid_string_returns_case(): void
    {
        $this->assertEquals(DocumentType::QUOTATION, DocumentType::tryFrom('quotation'));
    }

    #[Test]
    public function try_from_invalid_string_returns_null(): void
    {
        $this->assertNull(DocumentType::tryFrom('purchase_order'));
    }
}
