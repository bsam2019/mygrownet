<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\InvestorStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvestorStatusTest extends TestCase
{
    public function test_prospective_can_be_created(): void
    {
        $status = InvestorStatus::prospective();
        $this->assertTrue($status->isProspective());
        $this->assertFalse($status->isCIU());
        $this->assertFalse($status->isShareholder());
        $this->assertFalse($status->isExited());
        $this->assertEquals('prospective', $status->value());
        $this->assertEquals('Prospective Investor', $status->label());
    }

    public function test_ciu_can_be_created(): void
    {
        $status = InvestorStatus::ciu();
        $this->assertTrue($status->isCIU());
        $this->assertEquals('ciu', $status->value());
        $this->assertEquals('Convertible Investment Unit', $status->label());
    }

    public function test_shareholder_can_be_created(): void
    {
        $status = InvestorStatus::shareholder();
        $this->assertTrue($status->isShareholder());
        $this->assertEquals('shareholder', $status->value());
        $this->assertEquals('Shareholder', $status->label());
    }

    public function test_exited_can_be_created(): void
    {
        $status = InvestorStatus::exited();
        $this->assertTrue($status->isExited());
        $this->assertEquals('exited', $status->value());
        $this->assertEquals('Exited', $status->label());
    }

    public function test_from_string_creates_correct_status(): void
    {
        $this->assertTrue(InvestorStatus::fromString('prospective')->isProspective());
        $this->assertTrue(InvestorStatus::fromString('ciu')->isCIU());
        $this->assertTrue(InvestorStatus::fromString('shareholder')->isShareholder());
        $this->assertTrue(InvestorStatus::fromString('exited')->isExited());
    }

    public function test_throws_exception_for_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InvestorStatus::fromString('invalid_status');
    }

    public function test_equality(): void
    {
        $a = InvestorStatus::shareholder();
        $b = InvestorStatus::fromString('shareholder');
        $c = InvestorStatus::ciu();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_string(): void
    {
        $this->assertEquals('shareholder', (string) InvestorStatus::shareholder());
        $this->assertEquals('exited', (string) InvestorStatus::exited());
    }
}
