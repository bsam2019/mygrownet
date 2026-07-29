<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\ReportSchedule;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReportScheduleTest extends TestCase
{
    private ReportSchedule $schedule;

    protected function setUp(): void
    {
        $this->schedule = new ReportSchedule(
            id: 1, businessId: 5, name: 'Monthly Report',
            reportType: 'profit_loss', frequency: 'monthly',
            recipients: ['admin@example.com'], format: 'pdf',
            isActive: true, lastRunAt: null, nextRunAt: null,
            createdAt: null, updatedAt: null,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->schedule->id);
        $this->assertSame('Monthly Report', $this->schedule->name);
        $this->assertSame(['admin@example.com'], $this->schedule->recipients);
    }

    #[Test]
    public function with_last_run_updates_dates()
    {
        $lastRun = new DateTimeImmutable('2026-01-01');
        $nextRun = new DateTimeImmutable('2026-02-01');

        $updated = $this->schedule->withLastRun($lastRun, $nextRun);

        $this->assertSame($lastRun, $updated->lastRunAt);
        $this->assertSame($nextRun, $updated->nextRunAt);
        $this->assertNotSame($this->schedule, $updated);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $s = ReportSchedule::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Weekly',
            'report_type' => 'balance_sheet', 'frequency' => 'weekly',
            'recipients' => ['a@b.com'],
        ]);

        $this->assertSame('Weekly', $s->name);
        $this->assertSame('balance_sheet', $s->reportType);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->schedule->toArray();

        $this->assertSame('Monthly Report', $array['name']);
        $this->assertSame('pdf', $array['format']);
    }
}
