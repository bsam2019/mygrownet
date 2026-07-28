<?php

namespace Tests\Feature\StockFlow;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

class InventoryServiceTest extends StockFlowTestCase
{
    public function test_can_create_item(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Test Item',
            'sku' => 'TST-001',
            'unit_price' => 25.00,
            'system_quantity' => 100,
            'unit' => 'pcs',
        ]);

        $this->assertNotNull($item);
        $this->assertGreaterThan(0, $item->id());
        $this->assertEquals('Test Item', $item->getName());
        $this->assertEquals(25.00, $item->getUnitPrice()->toFloat());
        $this->assertEquals(100, $item->getSystemQuantity());
    }

    public function test_create_item_records_opening_balance_movement(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Stock Item',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);

        $movements = $this->movementRepository->findByCompanyId(CompanyId::fromInt($this->companyId));
        $this->assertCount(1, $movements);
        $this->assertEquals('purchase_in', $movements[0]->getType()->value());
        $this->assertEquals(50, $movements[0]->getQuantity());
    }

    public function test_create_item_with_zero_stock_has_no_movement(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Zero Stock Item',
            'unit_price' => 10.00,
            'system_quantity' => 0,
        ]);

        $movements = $this->movementRepository->findByCompanyId(CompanyId::fromInt($this->companyId));
        $this->assertCount(0, $movements);
    }

    public function test_can_update_item(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Original Name',
            'unit_price' => 10.00,
        ]);

        $updated = $this->inventoryService->updateItem($item->id(), $this->companyId, [
            'name' => 'Updated Name',
            'unit_price' => 15.00,
        ]);

        $this->assertEquals('Updated Name', $updated->getName());
        $this->assertEquals(15.00, $updated->getUnitPrice()->toFloat());
    }

    public function test_update_item_denied_for_wrong_company(): void
    {
        $this->expectException(\App\Domain\StockFlow\Exceptions\OperationFailedException::class);

        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Test',
            'unit_price' => 10.00,
        ]);

        $this->inventoryService->updateItem($item->id(), 99999, [
            'name' => 'Hacked',
        ]);
    }

    public function test_can_adjust_stock(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Adjustable',
            'unit_price' => 10.00,
            'system_quantity' => 100,
        ]);

        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $result = $this->inventoryService->adjustStock(
            $item->id(),
            $this->companyId,
            150,
            'adjustment_in',
            'Stock increase',
            1,
        );

        $this->assertEquals(150, $result->getSystemQuantity());
    }

    public function test_adjust_stock_records_movement(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Tracked Item',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);

        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->inventoryService->adjustStock(
            $item->id(),
            $this->companyId,
            75,
            'adjustment_in',
            'Adding more',
            1,
        );

        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($item->id()));
        $adjustmentMovements = array_filter($movements, fn($m) => $m->getType()->value() === 'adjustment_in');
        $this->assertCount(1, $adjustmentMovements);
        $this->assertEquals(25, current($adjustmentMovements)->getQuantity());
    }

    public function test_can_delete_item(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Delete Me',
            'unit_price' => 5.00,
        ]);

        $this->inventoryService->deleteItem($item->id(), $this->companyId);

        $found = $this->inventoryService->getItemById($item->id(), $this->companyId);
        $this->assertNull($found);
    }

    public function test_delete_item_denied_for_wrong_company(): void
    {
        $this->expectException(\App\Domain\StockFlow\Exceptions\OperationFailedException::class);

        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Test',
            'unit_price' => 10.00,
        ]);

        $this->inventoryService->deleteItem($item->id(), 99999);
    }

    public function test_can_list_items_for_company(): void
    {
        $this->inventoryService->createItem($this->companyId, ['name' => 'Item A', 'unit_price' => 10.00]);
        $this->inventoryService->createItem($this->companyId, ['name' => 'Item B', 'unit_price' => 20.00]);

        $items = $this->inventoryService->getItemsForCompany($this->companyId);
        $this->assertCount(2, $items);
    }

    public function test_get_item_by_id_returns_null_for_wrong_company(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Hidden',
            'unit_price' => 10.00,
        ]);

        $result = $this->inventoryService->getItemById($item->id(), 99999);
        $this->assertNull($result);
    }

    public function test_can_count_items(): void
    {
        $this->inventoryService->createItem($this->companyId, ['name' => 'A', 'unit_price' => 1.00]);
        $this->inventoryService->createItem($this->companyId, ['name' => 'B', 'unit_price' => 2.00]);
        $this->inventoryService->createItem($this->companyId, ['name' => 'C', 'unit_price' => 3.00]);

        $count = $this->inventoryService->getItemCount($this->companyId);
        $this->assertEquals(3, $count);
    }

    public function test_inventory_valuation(): void
    {
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Widget',
            'unit_price' => 10.00,
            'system_quantity' => 5,
        ]);
        $this->inventoryService->createItem($this->companyId, [
            'name' => 'Gadget',
            'unit_price' => 20.00,
            'system_quantity' => 3,
        ]);

        $valuation = $this->inventoryService->getInventoryValuation($this->companyId);

        $this->assertEquals(2, $valuation['total_items_with_stock']);
        $this->assertEquals(110, $valuation['total_retail_value']); // (5*10) + (3*20)
    }

    public function test_expiring_items(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Expiring Soon',
            'unit_price' => 10.00,
            'system_quantity' => 10,
            'is_expirable' => true,
            'expiry_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $expiring = $this->inventoryService->getExpiringItems($this->companyId, 30);
        $this->assertCount(1, $expiring);
        $this->assertEqualsWithDelta(5, $expiring[0]['days_until_expiry'], 1);
    }
}
