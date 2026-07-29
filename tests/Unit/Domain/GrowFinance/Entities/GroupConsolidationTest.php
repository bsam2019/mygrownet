<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\GroupConsolidation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GroupConsolidationTest extends TestCase
{
    #[Test]
    public function create_returns_new_instance_with_defaults()
    {
        $gc = GroupConsolidation::create(groupId: 1, businessId: 5, period: '2026-Q1');

        $this->assertNull($gc->id);
        $this->assertSame(1, $gc->groupId);
        $this->assertSame('2026-Q1', $gc->period);
        $this->assertSame('draft', $gc->status);
    }

    #[Test]
    public function complete_changes_status()
    {
        $gc = GroupConsolidation::create(groupId: 1, businessId: 5, period: '2026-Q1');
        $completed = $gc->complete();

        $this->assertSame('completed', $completed->status);
        $this->assertNotNull($completed->consolidatedAt);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $gc = GroupConsolidation::reconstitute([
            'id' => 1, 'group_id' => 1, 'business_id' => 5, 'period' => '2026-Q1',
            'consolidated_data' => '{"revenue": 1000}', 'status' => 'completed',
        ]);

        $this->assertSame('2026-Q1', $gc->period);
        $this->assertSame('completed', $gc->status);
        $this->assertSame(['revenue' => 1000], $gc->consolidatedData);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $gc = GroupConsolidation::create(groupId: 1, businessId: 5, period: '2026-Q1');
        $array = $gc->toArray();

        $this->assertSame('2026-Q1', $array['period']);
        $this->assertSame('draft', $array['status']);
    }
}
