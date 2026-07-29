<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\Entities\Profile;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ProfileTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields(): void
    {
        $profile = Profile::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'organization_id' => 5,
            'name' => 'My Business',
            'address' => '123 Main St',
            'phone' => '+260977123456',
            'email' => 'info@biz.com',
            'logo' => 'logo.png',
            'signature' => 'sig.png',
            'prepared_by' => 'Alice',
            'tax_number' => 'TAX-001',
            'default_tax_rate' => '16.00',
            'default_discount_rate' => '5.00',
            'default_notes' => 'Thank you',
            'default_terms' => 'Net 30',
            'invoice_prefix' => 'INV',
            'invoice_next_number' => 5,
            'invoice_number_padding' => 5,
            'quotation_prefix' => 'QT',
            'quotation_next_number' => 3,
            'quotation_number_padding' => 4,
            'receipt_prefix' => 'RCP',
            'receipt_next_number' => 10,
            'receipt_number_padding' => 3,
            'delivery_note_prefix' => 'DN',
            'delivery_note_next_number' => 1,
            'delivery_note_number_padding' => 4,
            'default_template' => 'modern',
            'default_color' => '#2563eb',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-07-01 00:00:00',
        ]);

        $this->assertSame(1, $profile->id);
        $this->assertSame(42, $profile->userId);
        $this->assertSame(5, $profile->organizationId);
        $this->assertSame('My Business', $profile->name);
        $this->assertSame(16.0, $profile->defaultTaxRate);
        $this->assertSame('INV', $profile->invoicePrefix);
        $this->assertSame(5, $profile->invoiceNextNumber);
        $this->assertSame(5, $profile->invoiceNumberPadding);
    }

    #[Test]
    public function reconstitute_with_minimal_data(): void
    {
        $profile = Profile::reconstitute(['user_id' => 1]);
        $this->assertNull($profile->id);
        $this->assertSame(1, $profile->userId);
        $this->assertNull($profile->name);
        $this->assertNull($profile->defaultTaxRate);
    }

    #[Test]
    public function reconstitute_casts_float_fields(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'default_tax_rate' => '16',
            'default_discount_rate' => '5.5',
        ]);
        $this->assertSame(16.0, $profile->defaultTaxRate);
        $this->assertSame(5.5, $profile->defaultDiscountRate);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $profile = Profile::reconstitute([
            'id' => 10,
            'user_id' => 99,
            'name' => 'Test Biz',
            'invoice_prefix' => 'INV',
            'invoice_next_number' => 1,
            'default_tax_rate' => 16,
        ]);

        $arr = $profile->toArray();
        $this->assertSame(10, $arr['id']);
        $this->assertSame(99, $arr['user_id']);
        $this->assertSame('Test Biz', $arr['name']);
        $this->assertSame('INV', $arr['invoice_prefix']);
        $this->assertSame(1, $arr['invoice_next_number']);
        $this->assertSame(16.0, $arr['default_tax_rate']);
    }

    #[Test]
    public function generate_document_number_uses_prefix_and_padding(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'invoice_prefix' => 'INV',
            'invoice_next_number' => 23,
            'invoice_number_padding' => 5,
        ]);
        $this->assertSame('INV-00023', $profile->generateDocumentNumber('invoice'));
    }

    #[Test]
    public function generate_document_number_defaults_for_missing_type(): void
    {
        $profile = Profile::reconstitute(['user_id' => 1]);
        $this->assertSame('INV-0001', $profile->generateDocumentNumber('invoice'));
    }

    #[Test]
    public function generate_document_number_custom_type(): void
    {
        $profile = Profile::reconstitute(['user_id' => 1]);
        $this->assertSame('CRE-0001', $profile->generateDocumentNumber('credit_note'));
    }

    #[Test]
    public function generate_document_number_quotation(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'quotation_prefix' => 'QT',
            'quotation_next_number' => 7,
        ]);
        $this->assertSame('QT-0007', $profile->generateDocumentNumber('quotation'));
    }

    #[Test]
    public function generate_document_number_receipt(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'receipt_prefix' => 'RCP',
            'receipt_next_number' => 100,
            'receipt_number_padding' => 6,
        ]);
        $this->assertSame('RCP-000100', $profile->generateDocumentNumber('receipt'));
    }

    #[Test]
    public function generate_document_number_delivery_note(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'delivery_note_prefix' => 'DN',
            'delivery_note_next_number' => 3,
        ]);
        $this->assertSame('DN-0003', $profile->generateDocumentNumber('delivery_note'));
    }

    #[Test]
    public function with_incremented_number_returns_new_profile(): void
    {
        $profile = Profile::reconstitute([
            'user_id' => 1,
            'invoice_next_number' => 5,
        ]);
        $updated = $profile->withIncrementedNumber('invoice');
        $this->assertSame(5, $profile->invoiceNextNumber);
        $this->assertSame(6, $updated->invoiceNextNumber);
    }
}
