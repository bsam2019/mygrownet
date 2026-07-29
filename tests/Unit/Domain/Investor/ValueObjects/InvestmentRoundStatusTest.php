<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\InvestmentRoundStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvestmentRoundStatusTest extends TestCase
{
    public function test_draft_can_be_created(): void
    {
        $status = InvestmentRoundStatus::draft();
        $this->assertEquals('draft', $status->value());
        $this->assertEquals('Draft', $status->getDisplayName());
        $this->assertEquals('Draft', $status->label());
        $this->assertEquals('gray', $status->getBadgeColor());
    }

    public function test_active_can_be_created(): void
    {
        $status = InvestmentRoundStatus::active();
        $this->assertEquals('active', $status->value());
        $this->assertEquals('Active', $status->getDisplayName());
        $this->assertEquals('green', $status->getBadgeColor());
    }

    public function test_closed_can_be_created(): void
    {
        $status = InvestmentRoundStatus::closed();
        $this->assertEquals('closed', $status->value());
        $this->assertEquals('Closed', $status->getDisplayName());
        $this->assertEquals('yellow', $status->getBadgeColor());
    }

    public function test_completed_can_be_created(): void
    {
        $status = InvestmentRoundStatus::completed();
        $this->assertEquals('completed', $status->value());
        $this->assertEquals('Completed', $status->getDisplayName());
        $this->assertEquals('blue', $status->getBadgeColor());
    }

    public function test_from_creates_correct_status(): void
    {
        $this->assertTrue(InvestmentRoundStatus::from('draft')->equals(InvestmentRoundStatus::draft()));
        $this->assertTrue(InvestmentRoundStatus::from('active')->equals(InvestmentRoundStatus::active()));
        $this->assertTrue(InvestmentRoundStatus::from('closed')->equals(InvestmentRoundStatus::closed()));
        $this->assertTrue(InvestmentRoundStatus::from('completed')->equals(InvestmentRoundStatus::completed()));
    }

    public function test_throws_exception_for_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InvestmentRoundStatus::from('unknown');
    }

    public function test_equality(): void
    {
        $a = InvestmentRoundStatus::active();
        $b = InvestmentRoundStatus::from('active');
        $c = InvestmentRoundStatus::closed();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
