<?php

namespace Tests\Feature\StockFlow;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;

class PhysicalCountServiceTest extends StockFlowTestCase
{
    public function test_can_create_physical_count(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Countable',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Monthly Count',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $this->assertNotNull($count);
        $this->assertGreaterThan(0, $count->id());
        $this->assertTrue($count->isDraft());
        $this->assertEquals('Monthly Count', $count->getTitle());
    }

    public function test_create_count_snapshots_items(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Item A',
            'unit_price' => 10.00,
            'system_quantity' => 20,
        ]);
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Item B',
            'unit_price' => 15.00,
            'system_quantity' => 30,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Snapshot Test',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->assertCount(2, $countItems);
    }

    public function test_can_update_count_items(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Counted Item',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Update Test',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->assertCount(1, $countItems);

        $this->physicalCountService->updateCountItems($count->id(), [
            ['id' => $countItems[0]->id(), 'physical_quantity' => 45],
        ]);

        $updatedItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->assertEquals(45, $updatedItems[0]->getPhysicalQuantity());
        $this->assertEquals(-5, $updatedItems[0]->getVariance()); // 45 - 50
    }

    public function test_can_complete_physical_count(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Finalized',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Complete Test',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->physicalCountService->updateCountItems($count->id(), [
            ['id' => $countItems[0]->id(), 'physical_quantity' => 45],
        ]);

        $this->physicalCountService->completeCount($count->id(), $this->user->id);

        $completedCount = $this->physicalCountService->getCountById($count->id(), $this->companyId);
        $this->assertTrue($completedCount->isCompleted());

        // Verify stock movement recorded
        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($item->id()));
        $adjustments = array_filter($movements, fn($m) => $m->getType()->value() === 'physical_count');
        $this->assertCount(1, $adjustments);
    }

    public function test_complete_count_adjusts_stock_levels(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Adjusted',
            'unit_price' => 10.00,
            'system_quantity' => 100,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Adjustment Test',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->physicalCountService->updateCountItems($count->id(), [
            ['id' => $countItems[0]->id(), 'physical_quantity' => 80],
        ]);

        $this->physicalCountService->completeCount($count->id(), $this->user->id);

        $levels = $this->stockLevelProjector->getLevelsForCompany($this->companyId);
        $this->assertEquals(80, (float) $levels[$item->id()]['qty_on_hand']);
    }

    public function test_can_generate_audit_from_count(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Auditable',
            'unit_price' => 10.00,
            'system_quantity' => 100,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $count = $this->physicalCountService->createCount(
            $this->companyId,
            [
                'title' => 'Audit Test',
                'count_date' => now()->format('Y-m-d'),
            ],
            $this->user->id,
        );

        $countItems = $this->countRepository->getCountItems(PhysicalCountId::fromInt($count->id()));
        $this->physicalCountService->updateCountItems($count->id(), [
            ['id' => $countItems[0]->id(), 'physical_quantity' => 90],
        ]);

        $this->physicalCountService->completeCount($count->id(), $this->user->id);

        $audit = $this->physicalCountService->generateAudit($count->id());

        $this->assertNotNull($audit);
        $this->assertGreaterThan(0, $audit->id());
        $this->assertStringContainsString('Audit Test', $audit->getTitle());
    }

    public function test_can_get_counts_for_company(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Item',
            'unit_price' => 10.00,
        ]);
        $this->stockLevelProjector->rebuildForCompany($this->companyId);

        $this->physicalCountService->createCount($this->companyId, [
            'title' => 'Count 1',
            'count_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $this->physicalCountService->createCount($this->companyId, [
            'title' => 'Count 2',
            'count_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $counts = $this->physicalCountService->getCountsForCompany($this->companyId);
        $this->assertCount(2, $counts);
    }
}
