<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\AccountingPeriod;
use App\Domain\GrowFinance\ValueObjects\PeriodStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountingPeriodTest extends TestCase
{
    private DateTimeImmutable $now;
    private AccountingPeriod $openPeriod;

    protected function setUp(): void
    {
        $this->now = new DateTimeImmutable('2026-01-15');
        $this->openPeriod = new AccountingPeriod(
            id: 1,
            businessId: 5,
            fiscalYearId: 1,
            label: 'January 2026',
            startDate: new DateTimeImmutable('2026-01-01'),
            endDate: new DateTimeImmutable('2026-01-31'),
        );
    }

    #[Test]
    public function open_period_can_be_closed()
    {
        $closed = $this->openPeriod->close(42, $this->now);

        $this->assertSame(PeriodStatus::CLOSED, $closed->status);
        $this->assertSame(42, $closed->closedBy);
        $this->assertSame($this->now, $closed->closedAt);
        $this->assertSame($this->now, $closed->updatedAt);
    }

    #[Test]
    public function closed_period_can_be_reopened()
    {
        $closed = $this->openPeriod->close(42, $this->now);
        $reopened = $closed->reopen();

        $this->assertSame(PeriodStatus::OPEN, $reopened->status);
        $this->assertNull($reopened->closedBy);
        $this->assertNull($reopened->closedAt);
    }

    #[Test]
    public function cannot_close_locked_period()
    {
        $locked = new AccountingPeriod(
            id: 2, businessId: 5, fiscalYearId: 1,
            label: 'Locked', startDate: new DateTimeImmutable('2025-12-01'),
            endDate: new DateTimeImmutable('2025-12-31'), status: PeriodStatus::LOCKED,
        );

        $this->expectException(\DomainException::class);
        $locked->close(1, $this->now);
    }

    #[Test]
    public function cannot_reopen_open_period()
    {
        $this->expectException(\DomainException::class);
        $this->openPeriod->reopen();
    }

    #[Test]
    public function reconstitute_restores_closed_period()
    {
        $period = AccountingPeriod::reconstitute([
            'id' => 1,
            'business_id' => 5,
            'fiscal_year_id' => 1,
            'label' => 'January 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'closed',
            'closed_by' => 42,
            'closed_at' => '2026-01-15 10:00:00',
        ]);

        $this->assertSame(PeriodStatus::CLOSED, $period->status);
        $this->assertSame(42, $period->closedBy);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->openPeriod->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(5, $array['business_id']);
        $this->assertSame('open', $array['status']);
    }
}
