<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Quotation;
use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QuotationTest extends TestCase
{
    private Quotation $quotation;

    protected function setUp(): void
    {
        $this->quotation = new Quotation(
            id: 1, businessId: 5, customerId: 10, templateId: null,
            quotationNumber: 'Q-001', quotationDate: new DateTimeImmutable('2026-01-01'),
            validUntil: new DateTimeImmutable('2027-02-01'),
            status: QuotationStatus::DRAFT, subtotal: 1000.0, taxAmount: 160.0,
            discountAmount: 0.0, totalAmount: 1160.0, notes: null, terms: null,
            subject: null, convertedInvoiceId: null, sentAt: null, acceptedAt: null,
            rejectedAt: null, rejectionReason: null, createdAt: null, updatedAt: null,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame('Q-001', $this->quotation->quotationNumber);
        $this->assertSame(QuotationStatus::DRAFT, $this->quotation->status);
    }

    #[Test]
    public function is_expired_returns_false_when_not_expired()
    {
        $this->assertFalse($this->quotation->isExpired());
    }

    #[Test]
    public function is_expired_returns_true_when_valid_until_passed()
    {
        $expired = new Quotation(
            id: 2, businessId: 5, customerId: null, templateId: null,
            quotationNumber: 'Q-002', quotationDate: null,
            validUntil: new DateTimeImmutable('2020-01-01'),
            status: QuotationStatus::SENT, subtotal: 0.0, taxAmount: 0.0,
            discountAmount: 0.0, totalAmount: 100.0, notes: null, terms: null,
            subject: null, convertedInvoiceId: null, sentAt: null, acceptedAt: null,
            rejectedAt: null, rejectionReason: null, createdAt: null, updatedAt: null,
        );
        $this->assertTrue($expired->isExpired());
    }

    #[Test]
    public function is_expired_returns_false_when_status_is_accepted()
    {
        $accepted = new Quotation(
            id: 3, businessId: 5, customerId: null, templateId: null,
            quotationNumber: 'Q-003', quotationDate: null,
            validUntil: new DateTimeImmutable('2020-01-01'),
            status: QuotationStatus::ACCEPTED, subtotal: 0.0, taxAmount: 0.0,
            discountAmount: 0.0, totalAmount: 100.0, notes: null, terms: null,
            subject: null, convertedInvoiceId: null, sentAt: null, acceptedAt: null,
            rejectedAt: null, rejectionReason: null, createdAt: null, updatedAt: null,
        );
        $this->assertFalse($accepted->isExpired());
    }

    #[Test]
    public function get_days_until_expiry_returns_zero_when_expired()
    {
        $expired = new Quotation(
            id: 4, businessId: 5, customerId: null, templateId: null,
            quotationNumber: 'Q-004', quotationDate: null,
            validUntil: new DateTimeImmutable('2020-01-01'),
            status: QuotationStatus::SENT, subtotal: 0.0, taxAmount: 0.0,
            discountAmount: 0.0, totalAmount: 100.0, notes: null, terms: null,
            subject: null, convertedInvoiceId: null, sentAt: null, acceptedAt: null,
            rejectedAt: null, rejectionReason: null, createdAt: null, updatedAt: null,
        );
        $this->assertSame(0, $expired->getDaysUntilExpiry());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $q = Quotation::reconstitute([
            'id' => 1, 'business_id' => 5, 'quotation_number' => 'Q-001',
            'status' => 'accepted', 'subtotal' => 1000.0, 'total_amount' => 1160.0,
        ]);

        $this->assertSame(QuotationStatus::ACCEPTED, $q->status);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->quotation->toArray();

        $this->assertSame('Q-001', $array['quotation_number']);
        $this->assertSame('draft', $array['status']);
    }
}
