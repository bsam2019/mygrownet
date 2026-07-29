<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\FiscalYear;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FiscalYearTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $start = new DateTimeImmutable('2026-01-01');
        $end = new DateTimeImmutable('2026-12-31');
        $year = new FiscalYear(id: 1, businessId: 5, label: 'FY 2026', startDate: $start, endDate: $end);

        $this->assertSame(1, $year->id);
        $this->assertSame(5, $year->businessId);
        $this->assertSame('FY 2026', $year->label);
        $this->assertFalse($year->isClosed);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $year = FiscalYear::reconstitute([
            'id' => 1, 'business_id' => 5, 'label' => 'FY 2026',
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
            'is_closed' => true,
        ]);

        $this->assertSame('FY 2026', $year->label);
        $this->assertTrue($year->isClosed);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $start = new DateTimeImmutable('2026-01-01');
        $end = new DateTimeImmutable('2026-12-31');
        $year = new FiscalYear(id: 1, businessId: 5, label: 'FY 2026', startDate: $start, endDate: $end);
        $array = $year->toArray();

        $this->assertSame('FY 2026', $array['label']);
        $this->assertSame('2026-01-01', $array['start_date']);
    }
}
