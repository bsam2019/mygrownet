<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\InquiryStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InquiryStatusTest extends TestCase
{
    public function test_new_can_be_created(): void
    {
        $status = InquiryStatus::new();
        $this->assertEquals('new', $status->value());
        $this->assertEquals('New', $status->getDisplayName());
        $this->assertEquals('blue', $status->getBadgeColor());
    }

    public function test_contacted_can_be_created(): void
    {
        $status = InquiryStatus::contacted();
        $this->assertEquals('contacted', $status->value());
        $this->assertEquals('Contacted', $status->getDisplayName());
        $this->assertEquals('yellow', $status->getBadgeColor());
    }

    public function test_meeting_scheduled_can_be_created(): void
    {
        $status = InquiryStatus::meetingScheduled();
        $this->assertEquals('meeting_scheduled', $status->value());
        $this->assertEquals('Meeting Scheduled', $status->getDisplayName());
        $this->assertEquals('green', $status->getBadgeColor());
    }

    public function test_closed_can_be_created(): void
    {
        $status = InquiryStatus::closed();
        $this->assertEquals('closed', $status->value());
        $this->assertEquals('Closed', $status->getDisplayName());
        $this->assertEquals('gray', $status->getBadgeColor());
    }

    public function test_from_creates_correct_status(): void
    {
        $this->assertTrue(InquiryStatus::from('new')->equals(InquiryStatus::new()));
        $this->assertTrue(InquiryStatus::from('contacted')->equals(InquiryStatus::contacted()));
        $this->assertTrue(InquiryStatus::from('meeting_scheduled')->equals(InquiryStatus::meetingScheduled()));
        $this->assertTrue(InquiryStatus::from('closed')->equals(InquiryStatus::closed()));
    }

    public function test_throws_exception_for_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InquiryStatus::from('bogus');
    }

    public function test_equality(): void
    {
        $a = InquiryStatus::new();
        $b = InquiryStatus::from('new');
        $c = InquiryStatus::contacted();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
