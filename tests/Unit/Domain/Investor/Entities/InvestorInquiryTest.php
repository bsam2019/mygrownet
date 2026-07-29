<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\Entities;

use App\Domain\Investor\Entities\InvestorInquiry;
use App\Domain\Investor\ValueObjects\InquiryStatus;
use App\Domain\Investor\ValueObjects\InvestmentRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class InvestorInquiryTest extends TestCase
{
    public function test_create_sets_initial_state(): void
    {
        $range = InvestmentRange::from('100-250');
        $inquiry = InvestorInquiry::create(
            name: 'John Doe',
            email: 'john@example.com',
            phone: '+260971234567',
            investmentRange: $range,
            message: 'I want to invest'
        );

        $this->assertEquals('John Doe', $inquiry->getName());
        $this->assertEquals('john@example.com', $inquiry->getEmail());
        $this->assertEquals('+260971234567', $inquiry->getPhone());
        $this->assertTrue($inquiry->getInvestmentRange()->equals($range));
        $this->assertEquals('I want to invest', $inquiry->getMessage());
        $this->assertTrue($inquiry->getStatus()->equals(InquiryStatus::new()));
        $this->assertTrue($inquiry->isNew());
        $this->assertTrue($inquiry->isHighValue());
        $this->assertEquals(0, $inquiry->getId());
    }

    public function test_create_without_message(): void
    {
        $inquiry = InvestorInquiry::create(
            name: 'Jane Doe',
            email: 'jane@example.com',
            phone: '+260977654321',
            investmentRange: InvestmentRange::from('25-50')
        );

        $this->assertNull($inquiry->getMessage());
        $this->assertFalse($inquiry->isHighValue());
    }

    public function test_mark_as_contacted(): void
    {
        $inquiry = $this->createInquiry();
        $inquiry->markAsContacted();

        $this->assertTrue($inquiry->getStatus()->equals(InquiryStatus::contacted()));
        $this->assertFalse($inquiry->isNew());
    }

    public function test_schedule_meeting(): void
    {
        $inquiry = $this->createInquiry();
        $inquiry->markAsContacted();
        $inquiry->scheduleMeeting();

        $this->assertTrue($inquiry->getStatus()->equals(InquiryStatus::meetingScheduled()));
    }

    public function test_close(): void
    {
        $inquiry = $this->createInquiry();
        $inquiry->close();

        $this->assertTrue($inquiry->getStatus()->equals(InquiryStatus::closed()));
    }

    public function test_from_persistence_restores_state(): void
    {
        $now = new DateTimeImmutable();
        $range = InvestmentRange::from('250+');
        $status = InquiryStatus::contacted();

        $inquiry = InvestorInquiry::fromPersistence(
            id: 42,
            name: 'Restored Name',
            email: 'restored@example.com',
            phone: '+260971111111',
            investmentRange: $range,
            message: 'Restored message',
            status: $status,
            createdAt: $now,
            updatedAt: $now
        );

        $this->assertEquals(42, $inquiry->getId());
        $this->assertEquals('Restored Name', $inquiry->getName());
        $this->assertTrue($inquiry->getStatus()->equals($status));
        $this->assertTrue($inquiry->getCreatedAt() === $now);
    }

    private function createInquiry(): InvestorInquiry
    {
        return InvestorInquiry::create(
            name: 'Test',
            email: 'test@test.com',
            phone: '+260970000000',
            investmentRange: InvestmentRange::from('25-50')
        );
    }
}
