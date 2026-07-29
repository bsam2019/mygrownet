<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\FixedAsset;
use App\Domain\GrowFinance\Services\DepreciationEngine;
use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DepreciationEngineTest extends TestCase
{
    private DepreciationEngine $engine;
    private FixedAsset $straightLineAsset;
    private FixedAsset $reducingBalanceAsset;

    protected function setUp(): void
    {
        $this->engine = new DepreciationEngine();

        $this->straightLineAsset = new FixedAsset(
            id: 1, businessId: 5, name: 'Machine',
            category: 'Equipment', purchaseDate: new DateTimeImmutable('2025-01-01'),
            cost: 120000.0, residualValue: 0.0, usefulLifeMonths: 60,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 20000.0,
            status: AssetStatus::ACTIVE,
        );

        $this->reducingBalanceAsset = new FixedAsset(
            id: 2, businessId: 5, name: 'Vehicle',
            category: 'Vehicles', purchaseDate: new DateTimeImmutable('2024-01-01'),
            cost: 100000.0, residualValue: 10000.0, usefulLifeMonths: 60,
            depreciationMethod: DepreciationMethod::REDUCING_BALANCE,
            depreciationRate: 25.0, accumulatedDepreciation: 20000.0,
            status: AssetStatus::ACTIVE,
        );
    }

    #[Test]
    public function straight_line_returns_correct_monthly_depreciation()
    {
        $amount = $this->engine->straightLine($this->straightLineAsset);
        $this->assertSame(2000.0, $amount);
    }

    #[Test]
    public function reducing_balance_returns_correct_monthly_depreciation()
    {
        $amount = $this->engine->reducingBalance($this->reducingBalanceAsset);
        $nbv = $this->reducingBalanceAsset->getNetBookValue();
        $expected = min(($nbv * 0.25) / 12, $this->reducingBalanceAsset->getDepreciableAmount() - $this->reducingBalanceAsset->accumulatedDepreciation);
        $this->assertSame(round($expected, 2), round($amount, 2));
    }

    #[Test]
    public function compute_period_depreciation_returns_zero_for_inactive_asset()
    {
        $disposed = new FixedAsset(
            id: 3, businessId: 5, name: 'Disposed', category: null,
            purchaseDate: new DateTimeImmutable('2025-01-01'), cost: 10000.0,
            residualValue: 0.0, usefulLifeMonths: 12,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 5000.0, status: AssetStatus::DISPOSED,
        );
        $amount = $this->engine->computePeriodDepreciation($disposed, new DateTimeImmutable('2026-01-01'));
        $this->assertSame(0.0, $amount);
    }

    #[Test]
    public function compute_period_depreciation_returns_zero_for_future_date()
    {
        $amount = $this->engine->computePeriodDepreciation($this->straightLineAsset, new DateTimeImmutable('2024-01-01'));
        $this->assertSame(0.0, $amount);
    }

    #[Test]
    public function compute_period_depreciation_returns_straight_line_for_active_asset()
    {
        $amount = $this->engine->computePeriodDepreciation($this->straightLineAsset, new DateTimeImmutable('2026-01-01'));
        $this->assertSame(2000.0, $amount);
    }

    #[Test]
    public function generate_schedule_returns_expected_number_of_entries()
    {
        $schedule = $this->engine->generateSchedule($this->straightLineAsset);
        $this->assertNotEmpty($schedule);
        $this->assertArrayHasKey('period_date', $schedule[0]);
        $this->assertArrayHasKey('depreciation_amount', $schedule[0]);
    }

    #[Test]
    public function generate_schedule_returns_empty_for_fully_depreciated()
    {
        $fullyDepr = new FixedAsset(
            id: 4, businessId: 5, name: 'Full', category: null,
            purchaseDate: new DateTimeImmutable('2020-01-01'), cost: 10000.0,
            residualValue: 0.0, usefulLifeMonths: 12,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 10000.0, status: AssetStatus::FULLY_DEPRECIATED,
        );
        $schedule = $this->engine->generateSchedule($fullyDepr);
        $this->assertEmpty($schedule);
    }

    #[Test]
    public function compute_period_depreciation_returns_zero_for_fully_depreciated()
    {
        $fullyDepr = new FixedAsset(
            id: 5, businessId: 5, name: 'Full', category: null,
            purchaseDate: new DateTimeImmutable('2020-01-01'), cost: 10000.0,
            residualValue: 0.0, usefulLifeMonths: 12,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 10000.0, status: AssetStatus::ACTIVE,
        );
        $amount = $this->engine->computePeriodDepreciation($fullyDepr, new DateTimeImmutable('2026-01-01'));
        $this->assertSame(0.0, $amount);
    }
}
