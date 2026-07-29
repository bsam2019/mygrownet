<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class QuotationStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('draft', QuotationStatus::DRAFT->value);
        $this->assertSame('sent', QuotationStatus::SENT->value);
        $this->assertSame('accepted', QuotationStatus::ACCEPTED->value);
        $this->assertSame('rejected', QuotationStatus::REJECTED->value);
        $this->assertSame('expired', QuotationStatus::EXPIRED->value);
        $this->assertSame('converted', QuotationStatus::CONVERTED->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(QuotationStatus::DRAFT, QuotationStatus::from('draft'));
        $this->assertSame(QuotationStatus::SENT, QuotationStatus::from('sent'));
        $this->assertSame(QuotationStatus::ACCEPTED, QuotationStatus::from('accepted'));
        $this->assertSame(QuotationStatus::REJECTED, QuotationStatus::from('rejected'));
        $this->assertSame(QuotationStatus::EXPIRED, QuotationStatus::from('expired'));
        $this->assertSame(QuotationStatus::CONVERTED, QuotationStatus::from('converted'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        QuotationStatus::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Draft', QuotationStatus::DRAFT->label());
        $this->assertSame('Sent', QuotationStatus::SENT->label());
        $this->assertSame('Accepted', QuotationStatus::ACCEPTED->label());
        $this->assertSame('Rejected', QuotationStatus::REJECTED->label());
        $this->assertSame('Expired', QuotationStatus::EXPIRED->label());
        $this->assertSame('Converted to Invoice', QuotationStatus::CONVERTED->label());
    }

    #[Test]
    public function color_returns_correct_string()
    {
        $this->assertSame('gray', QuotationStatus::DRAFT->color());
        $this->assertSame('blue', QuotationStatus::SENT->color());
        $this->assertSame('emerald', QuotationStatus::ACCEPTED->color());
        $this->assertSame('red', QuotationStatus::REJECTED->color());
        $this->assertSame('amber', QuotationStatus::EXPIRED->color());
        $this->assertSame('indigo', QuotationStatus::CONVERTED->color());
    }

    #[Test]
    public function can_edit_returns_correctly()
    {
        $this->assertTrue(QuotationStatus::DRAFT->canEdit());
        $this->assertTrue(QuotationStatus::SENT->canEdit());
        $this->assertFalse(QuotationStatus::ACCEPTED->canEdit());
        $this->assertFalse(QuotationStatus::REJECTED->canEdit());
        $this->assertFalse(QuotationStatus::EXPIRED->canEdit());
        $this->assertFalse(QuotationStatus::CONVERTED->canEdit());
    }

    #[Test]
    public function can_convert_returns_correctly()
    {
        $this->assertTrue(QuotationStatus::ACCEPTED->canConvert());
        $this->assertFalse(QuotationStatus::DRAFT->canConvert());
        $this->assertFalse(QuotationStatus::SENT->canConvert());
        $this->assertFalse(QuotationStatus::REJECTED->canConvert());
    }

    #[Test]
    public function can_send_returns_correctly()
    {
        $this->assertTrue(QuotationStatus::DRAFT->canSend());
        $this->assertTrue(QuotationStatus::SENT->canSend());
        $this->assertFalse(QuotationStatus::ACCEPTED->canSend());
        $this->assertFalse(QuotationStatus::REJECTED->canSend());
    }
}
