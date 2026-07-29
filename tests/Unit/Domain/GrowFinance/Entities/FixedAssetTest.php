<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\FixedAsset;
use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use App\Domain\GrowFinance\ValueObjects\DepreciationMethod;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FixedAssetTest extends TestCase
{
    private FixedAsset $asset;

    protected function setUp(): void
    {
        $this->asset = new FixedAsset(
            id: 1,
            businessId: 5,
            name: 'Delivery Truck',
            category: 'Vehicles',
            purchaseDate: new DateTimeImmutable('2025-01-01'),
            cost: 50000.0,
            residualValue: 5000.0,
            usefulLifeMonths: 60,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null,
            accumulatedDepreciation: 10000.0,
            status: AssetStatus::ACTIVE,
        );
    }

    #[Test]
    public function constructor_sets_properties()
    {
        $this->assertSame(1, $this->asset->id);
        $this->assertSame('Delivery Truck', $this->asset->name);
        $this->assertSame(50000.0, $this->asset->cost);
    }

    #[Test]
    public function get_net_book_value_returns_cost_minus_accumulated()
    {
        $this->assertSame(40000.0, $this->asset->getNetBookValue());
    }

    #[Test]
    public function get_net_book_value_clamps_to_zero()
    {
        $asset = new FixedAsset(
            id: 2, businessId: 5, name: 'Old Machine', category: null,
            purchaseDate: new DateTimeImmutable('2020-01-01'), cost: 10000.0,
            residualValue: 0.0, usefulLifeMonths: 12,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 15000.0, status: AssetStatus::ACTIVE,
        );
        $this->assertSame(0.0, $asset->getNetBookValue());
    }

    #[Test]
    public function get_depreciable_amount_returns_cost_minus_residual()
    {
        $this->assertSame(45000.0, $this->asset->getDepreciableAmount());
    }

    #[Test]
    public function monthly_straight_line_depreciation_is_correct()
    {
        $this->assertSame(750.0, $this->asset->getMonthlyStraightLineDepreciation());
    }

    #[Test]
    public function is_fully_depreciated_returns_false()
    {
        $this->assertFalse($this->asset->isFullyDepreciated());
    }

    #[Test]
    public function is_fully_depreciated_returns_true()
    {
        $asset = new FixedAsset(
            id: 3, businessId: 5, name: 'Fully Depr', category: null,
            purchaseDate: new DateTimeImmutable('2020-01-01'), cost: 10000.0,
            residualValue: 0.0, usefulLifeMonths: 24,
            depreciationMethod: DepreciationMethod::STRAIGHT_LINE,
            depreciationRate: null, accumulatedDepreciation: 10000.0, status: AssetStatus::FULLY_DEPRECIATED,
        );
        $this->assertTrue($asset->isFullyDepreciated());
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $asset = FixedAsset::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Car',
            'purchase_date' => '2025-01-01', 'cost' => 30000.0,
            'residual_value' => 3000.0, 'useful_life_months' => 60,
            'depreciation_method' => 'straight_line',
            'accumulated_depreciation' => 5000.0, 'status' => 'active',
        ]);

        $this->assertSame('Car', $asset->name);
        $this->assertSame(30000.0, $asset->cost);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $array = $this->asset->toArray();

        $this->assertSame('Delivery Truck', $array['name']);
        $this->assertSame(40000.0, $array['net_book_value']);
    }
}
