<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\ValueObjects;

use App\Domain\QuickInvoice\ValueObjects\DocumentNumber;
use App\Domain\QuickInvoice\ValueObjects\DocumentType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DocumentNumberTest extends TestCase
{
    #[Test]
    public function generate_for_invoice_starts_with_inv(): void
    {
        $number = DocumentNumber::generate(DocumentType::INVOICE);
        $this->assertStringStartsWith('INV-', $number->value());
    }

    #[Test]
    public function generate_for_delivery_note_starts_with_dn(): void
    {
        $number = DocumentNumber::generate(DocumentType::DELIVERY_NOTE);
        $this->assertStringStartsWith('DN-', $number->value());
    }

    #[Test]
    public function generate_for_quotation_starts_with_qt(): void
    {
        $number = DocumentNumber::generate(DocumentType::QUOTATION);
        $this->assertStringStartsWith('QT-', $number->value());
    }

    #[Test]
    public function generate_for_receipt_starts_with_rcp(): void
    {
        $number = DocumentNumber::generate(DocumentType::RECEIPT);
        $this->assertStringStartsWith('RCP-', $number->value());
    }

    #[Test]
    public function generate_contains_date_and_random(): void
    {
        $number = DocumentNumber::generate(DocumentType::INVOICE);
        $this->assertMatchesRegularExpression('/^INV-\d{6}-[A-Z0-9]{4}$/', $number->value());
    }

    #[Test]
    public function from_string_returns_trimmed_value(): void
    {
        $number = DocumentNumber::fromString('  INV-001  ');
        $this->assertSame('INV-001', $number->value());
    }

    #[Test]
    public function from_string_preserves_value(): void
    {
        $number = DocumentNumber::fromString('CUSTOM-FMT-001');
        $this->assertSame('CUSTOM-FMT-001', $number->value());
    }

    #[Test]
    public function to_string_returns_value(): void
    {
        $number = DocumentNumber::fromString('INV-001');
        $this->assertSame('INV-001', (string) $number);
    }

    #[Test]
    public function generate_unique_values(): void
    {
        $a = DocumentNumber::generate(DocumentType::INVOICE);
        $b = DocumentNumber::generate(DocumentType::INVOICE);
        $this->assertNotSame($a->value(), $b->value());
    }
}
