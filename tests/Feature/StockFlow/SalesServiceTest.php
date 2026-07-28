<?php

namespace Tests\Feature\StockFlow;

use App\Domain\StockFlow\Exceptions\InsufficientStockException;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;

class SalesServiceTest extends StockFlowTestCase
{
    public function test_can_create_sale(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Widget',
            'unit_price' => 25.00,
            'system_quantity' => 100,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $sale = $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 60.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 25.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $this->assertNotNull($sale);
        $this->assertGreaterThan(0, $sale->id());
        $this->assertEquals(50.00, $sale->getTotal()->toFloat());
        $this->assertEquals(60.00, $sale->getAmountTendered()->toFloat());
        $this->assertEquals(10.00, $sale->getChangeDue()->toFloat());
    }

    public function test_sale_deducts_stock(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Deductible',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 30.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 3, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $levels = $this->stockLevelProjector->getLevelsForCompany($this->companyId);
        $this->assertArrayHasKey($item->id(), $levels);
        $this->assertEquals(47, (float) $levels[$item->id()]['qty_on_hand']);
    }

    public function test_sale_creates_stock_movements(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Tracked',
            'unit_price' => 15.00,
            'system_quantity' => 20,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 30.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 15.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $movements = $this->movementRepository->findByItemId(ItemId::fromInt($item->id()));
        $saleMovements = array_filter($movements, fn($m) => $m->getType()->value() === 'sale_out');
        $this->assertCount(1, $saleMovements);
        $this->assertEquals(-2, current($saleMovements)->getQuantity());
    }

    public function test_sale_with_insufficient_stock_throws_exception(): void
    {
        $this->expectException(InsufficientStockException::class);

        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Limited',
            'unit_price' => 10.00,
            'system_quantity' => 5,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 100.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 10, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);
    }

    public function test_can_get_sale_by_id(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Widget',
            'unit_price' => 10.00,
            'system_quantity' => 10,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $sale = $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 20.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $found = $this->salesService->getSaleById($sale->id(), $this->companyId);
        $this->assertNotNull($found);
        $this->assertEquals($sale->id(), $found->id());
    }

    public function test_get_sale_by_id_returns_null_for_wrong_company(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Widget',
            'unit_price' => 10.00,
            'system_quantity' => 10,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $sale = $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 20.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $found = $this->salesService->getSaleById($sale->id(), 99999);
        $this->assertNull($found);
    }

    public function test_can_get_sales_for_company(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Widget',
            'unit_price' => 10.00,
            'system_quantity' => 50,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 20.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 30.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 3, 'unit_price' => 10.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $sales = $this->salesService->getSalesForCompany($this->companyId);
        $this->assertCount(2, $sales);
    }

    public function test_cash_sale_records_cash_register_entry(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Cash Item',
            'unit_price' => 100.00,
            'system_quantity' => 10,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'cash',
            'amount_tendered' => 200.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 2, 'unit_price' => 100.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $registers = $this->cashRegisterRepository->findByCompanyId(CompanyId::fromInt($this->companyId));
        $this->assertCount(1, $registers);
        $this->assertEquals(200.00, $registers[0]->getTotalSales()->toFloat());
    }

    public function test_non_cash_sale_does_not_record_register(): void
    {
        $item = $this->inventoryService->createItem($this->companyId, [
            'name' => 'Card Item',
            'unit_price' => 50.00,
            'system_quantity' => 10,
        ]);
        $this->stockLevelProjector->rebuildForItem($this->companyId, $item->id());

        $this->salesService->createSale($this->companyId, [
            'payment_method' => 'card',
            'amount_tendered' => 50.00,
            'items' => [
                ['sa_item_id' => $item->id(), 'quantity' => 1, 'unit_price' => 50.00],
            ],
            'sale_date' => now()->format('Y-m-d'),
        ], $this->user->id);

        $registers = $this->cashRegisterRepository->findByCompanyId(CompanyId::fromInt($this->companyId));
        $this->assertCount(0, $registers);
    }
}
