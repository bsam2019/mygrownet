<?php

namespace Tests\Feature\StockFlow;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

class PurchasingServiceTest extends StockFlowTestCase
{
    public function test_can_create_supplier(): void
    {
        $supplier = $this->purchasingService->createSupplier($this->companyId, [
            'name' => 'Acme Supplies',
            'contact_person' => 'John Doe',
            'phone' => '+260977123456',
            'email' => 'john@acme.com',
            'address' => '123 Main St',
            'payment_terms' => 'Net 30',
        ]);

        $this->assertNotNull($supplier);
        $this->assertGreaterThan(0, $supplier->getId()->toInt());
        $this->assertEquals('Acme Supplies', $supplier->getName());
    }

    public function test_can_get_suppliers_for_company(): void
    {
        $this->purchasingService->createSupplier($this->companyId, ['name' => 'Supplier A']);
        $this->purchasingService->createSupplier($this->companyId, ['name' => 'Supplier B']);

        $suppliers = $this->purchasingService->getSuppliersForCompany($this->companyId);
        $this->assertCount(2, $suppliers);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = $this->purchasingService->createSupplier($this->companyId, [
            'name' => 'Old Name',
        ]);

        $updated = $this->purchasingService->updateSupplier($supplier->getId()->toInt(), [
            'name' => 'New Name',
            'phone' => '+260977654321',
        ]);

        $this->assertEquals('New Name', $updated->getName());
        $this->assertEquals('+260977654321', $updated->getPhone());
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = $this->purchasingService->createSupplier($this->companyId, [
            'name' => 'Delete Me',
        ]);

        $this->purchasingService->deleteSupplier($supplier->getId()->toInt());

        $suppliers = $this->purchasingService->getSuppliersForCompany($this->companyId);
        $this->assertCount(0, $suppliers);
    }

    public function test_can_create_purchase_order(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Order Item',
            'unit_price' => 15.00,
        ]);

        $order = $this->purchasingService->createPurchaseOrder($this->companyId, [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 10, 'unit_cost' => 12.00],
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 5, 'unit_cost' => 14.00],
            ],
        ]);

        $this->assertNotNull($order);
        $this->assertGreaterThan(0, $order->id());
        $this->assertEquals(190.00, $order->getSubtotal()->toFloat()); // 10*12 + 5*14
        $this->assertCount(2, $order->getItems());
    }

    public function test_can_receive_purchase_order(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Receivable',
            'unit_price' => 10.00,
            'system_quantity' => 0,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $order = $this->purchasingService->createPurchaseOrder($this->companyId, [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 20, 'unit_cost' => 8.00],
            ],
        ]);

        $this->purchasingService->receiveOrder($order->id(), $this->companyId, [
            ['sa_item_id' => $item->id(), 'quantity_received' => 20, 'unit_cost' => 8.00],
        ], $this->user->id);

        $levels = $this->stockLevelProjector->getLevelsForCompany($this->companyId);
        $this->assertArrayHasKey($item->id(), $levels);
        $this->assertEquals(20, (float) $levels[$item->id()]['qty_on_hand']);
    }

    public function test_receive_order_creates_stock_movement(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'PO Tracked',
            'unit_price' => 10.00,
            'system_quantity' => 0,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $order = $this->purchasingService->createPurchaseOrder($this->companyId, [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 10, 'unit_cost' => 8.00],
            ],
        ]);

        $this->purchasingService->receiveOrder($order->id(), $this->companyId, [
            ['sa_item_id' => $item->id(), 'quantity_received' => 10, 'unit_cost' => 8.00],
        ], $this->user->id);

        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($item->id()));
        $poMovements = array_filter($movements, fn($m) => $m->getType()->value() === 'purchase_in');
        $this->assertCount(1, $poMovements);
        $this->assertEquals(10, current($poMovements)->getQuantity());
    }

    public function test_can_get_orders_for_company(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'PO Item',
            'unit_price' => 10.00,
        ]);

        $this->purchasingService->createPurchaseOrder($this->companyId, [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 5, 'unit_cost' => 10.00],
            ],
        ]);

        $orders = $this->purchasingService->getOrdersForCompany($this->companyId);
        $this->assertCount(1, $orders);
    }

    public function test_get_order_by_id_returns_null_for_wrong_company(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'PO Item',
            'unit_price' => 10.00,
        ]);

        $order = $this->purchasingService->createPurchaseOrder($this->companyId, [
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity_ordered' => 5, 'unit_cost' => 10.00],
            ],
        ]);

        $found = $this->purchasingService->getOrderById($order->id(), 99999);
        $this->assertNull($found);
    }
}
