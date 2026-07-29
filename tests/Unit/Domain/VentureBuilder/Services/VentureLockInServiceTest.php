<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\Services\VentureLockInService;
use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureLockInServiceTest extends TestCase
{
    private VentureLockInService $service;

    protected function setUp(): void
    {
        $this->service = new VentureLockInService();
    }

    #[Test]
    public function lock_in_period_is_12_months(): void
    {
        $this->assertSame(12, VentureLockInService::LOCK_IN_MONTHS);
    }

    #[Test]
    public function is_lock_in_period_active_returns_true_when_within_lock_in(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('-1 month'),
        );

        $this->assertTrue($this->service->isLockInPeriodActive($investment));
    }

    #[Test]
    public function is_lock_in_period_active_returns_false_when_lock_in_expired(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('-13 months'),
        );

        $this->assertFalse($this->service->isLockInPeriodActive($investment));
    }

    #[Test]
    public function get_lock_in_end_date_uses_payment_confirmed_at(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('2026-01-01'),
        );

        $endDate = $this->service->getLockInEndDate($investment);
        $this->assertSame('2027-01-01', $endDate->format('Y-m-d'));
    }

    #[Test]
    public function get_lock_in_end_date_falls_back_to_created_at(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::pending(),
            createdAt: new DateTimeImmutable('2026-06-01'),
        );

        $endDate = $this->service->getLockInEndDate($investment);
        $this->assertSame('2027-06-01', $endDate->format('Y-m-d'));
    }

    #[Test]
    public function get_remaining_lock_in_days_returns_zero_when_expired(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('-13 months'),
        );

        $this->assertSame(0, $this->service->getRemainingLockInDays($investment));
    }

    #[Test]
    public function get_remaining_lock_in_days_returns_positive_when_active(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('+6 months'),
        );

        $remaining = $this->service->getRemainingLockInDays($investment);
        $this->assertGreaterThan(0, $remaining);
    }

    #[Test]
    public function assert_not_locked_does_not_throw_when_lock_in_expired(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('-13 months'),
        );

        $this->service->assertNotLocked($investment);
        $this->assertTrue(true);
    }

    #[Test]
    public function assert_not_locked_throws_when_lock_in_active(): void
    {
        $investment = new Investment(
            ventureId: 1,
            userId: 1,
            amount: 1000.0,
            status: InvestmentStatus::confirmed(),
            paymentConfirmedAt: new DateTimeImmutable('-1 month'),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('lock-in period');

        $this->service->assertNotLocked($investment);
    }
}
