<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\DepreciationEntry;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DepreciationEntryTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $date = new DateTimeImmutable('2026-01-31');
        $entry = new DepreciationEntry(
            id: 1, assetId: 10, periodDate: $date,
            depreciationAmount: 500.0, accumulatedDepreciation: 2500.0, netBookValue: 9500.0
        );

        $this->assertSame(1, $entry->id);
        $this->assertSame(10, $entry->assetId);
        $this->assertSame(500.0, $entry->depreciationAmount);
        $this->assertSame(2500.0, $entry->accumulatedDepreciation);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $entry = DepreciationEntry::reconstitute([
            'id' => 1, 'asset_id' => 10, 'period_date' => '2026-01-31',
            'depreciation_amount' => 500.0, 'accumulated_depreciation' => 2000.0,
            'net_book_value' => 8000.0,
        ]);

        $this->assertSame(10, $entry->assetId);
        $this->assertSame(2000.0, $entry->accumulatedDepreciation);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $date = new DateTimeImmutable('2026-01-31');
        $entry = new DepreciationEntry(id: 1, assetId: 10, periodDate: $date, depreciationAmount: 500.0, accumulatedDepreciation: 2000.0, netBookValue: 8000.0);
        $array = $entry->toArray();

        $this->assertSame(10, $array['asset_id']);
        $this->assertSame(500.0, $array['depreciation_amount']);
    }
}
