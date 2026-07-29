<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Investor\ValueObjects;

use App\Domain\Investor\ValueObjects\ReportType;
use PHPUnit\Framework\TestCase;

class ReportTypeTest extends TestCase
{
    public function test_monthly_case(): void
    {
        $type = ReportType::MONTHLY;
        $this->assertEquals('monthly', $type->value);
        $this->assertEquals('Monthly Report', $type->label());
    }

    public function test_quarterly_case(): void
    {
        $type = ReportType::QUARTERLY;
        $this->assertEquals('quarterly', $type->value);
        $this->assertEquals('Quarterly Report', $type->label());
    }

    public function test_annual_case(): void
    {
        $type = ReportType::ANNUAL;
        $this->assertEquals('annual', $type->value);
        $this->assertEquals('Annual Report', $type->label());
    }

    public function test_all_returns_all_cases(): void
    {
        $all = ReportType::all();
        $this->assertCount(3, $all);
        $this->assertContains(ReportType::MONTHLY, $all);
        $this->assertContains(ReportType::QUARTERLY, $all);
        $this->assertContains(ReportType::ANNUAL, $all);
    }
}
