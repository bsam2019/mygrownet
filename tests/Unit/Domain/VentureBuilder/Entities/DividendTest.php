<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Dividend;
use App\Domain\VentureBuilder\ValueObjects\DividendStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DividendTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $dividend = new Dividend(
            ventureId: 1,
            shareholderId: 5,
            amount: 500.0,
            status: DividendStatus::declared(),
        );

        $this->assertSame(1, $dividend->ventureId);
        $this->assertSame(5, $dividend->shareholderId);
        $this->assertSame(500.0, $dividend->amount);
        $this->assertTrue($dividend->status->isDeclared());
        $this->assertNull($dividend->id);
    }

    #[Test]
    public function is_paid_delegates_to_status(): void
    {
        $paid = new Dividend(ventureId: 1, shareholderId: 1, amount: 100.0, status: DividendStatus::paid());
        $this->assertTrue($paid->isPaid());
    }

    #[Test]
    public function is_pending_returns_true_when_declared(): void
    {
        $declared = new Dividend(ventureId: 1, shareholderId: 1, amount: 100.0, status: DividendStatus::declared());
        $this->assertTrue($declared->isPending());
    }

    #[Test]
    public function is_pending_returns_false_when_paid(): void
    {
        $paid = new Dividend(ventureId: 1, shareholderId: 1, amount: 100.0, status: DividendStatus::paid());
        $this->assertFalse($paid->isPending());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 7,
            'venture_id' => 1,
            'shareholder_id' => 5,
            'dividend_period' => 'Q1 2026',
            'declaration_date' => '2026-04-01 00:00:00',
            'amount' => 500.0,
            'status' => 'declared',
            'notes' => 'First quarter dividend',
        ];

        $dividend = Dividend::reconstitute($data);

        $this->assertSame(7, $dividend->id);
        $this->assertSame('Q1 2026', $dividend->dividendPeriod);
        $this->assertSame(500.0, $dividend->amount);
        $this->assertTrue($dividend->status->isDeclared());
        $this->assertSame('First quarter dividend', $dividend->notes);
    }

    #[Test]
    public function reconstitute_defaults_status_to_declared(): void
    {
        $dividend = Dividend::reconstitute([
            'venture_id' => 1,
            'shareholder_id' => 1,
            'amount' => 100.0,
        ]);

        $this->assertTrue($dividend->status->isDeclared());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $dividend = new Dividend(
            ventureId: 1,
            shareholderId: 5,
            amount: 500.0,
            status: DividendStatus::paid(),
            id: 7,
            dividendPeriod: 'Q1 2026',
            paidAt: new DateTimeImmutable('2026-04-15 14:00:00'),
        );

        $arr = $dividend->toArray();

        $this->assertSame(7, $arr['id']);
        $this->assertSame(1, $arr['venture_id']);
        $this->assertSame(5, $arr['shareholder_id']);
        $this->assertSame('paid', $arr['status']);
        $this->assertSame('Q1 2026', $arr['dividend_period']);
        $this->assertSame('2026-04-15 14:00:00', $arr['paid_at']);
    }
}
