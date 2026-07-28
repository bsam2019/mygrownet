<?php

namespace Tests\Unit\StockFlow;

use App\Domain\StockFlow\Entities\Item;
use App\Domain\StockFlow\Entities\Sale;
use App\Domain\StockFlow\Entities\SaleItem;
use App\Domain\StockFlow\Entities\StockMovement;
use App\Domain\StockFlow\Entities\Audit;
use App\Domain\StockFlow\Entities\AuditItem;
use App\Domain\StockFlow\Entities\CashRegister;
use App\Domain\StockFlow\Entities\PhysicalCount;
use App\Domain\StockFlow\Entities\CountItem;
use App\Domain\StockFlow\Entities\PurchaseOrder;
use App\Domain\StockFlow\Entities\PurchaseOrderItem;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\DepartmentId;
use App\Domain\StockFlow\ValueObjects\BinId;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\SaleId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use App\Domain\StockFlow\ValueObjects\AuditStatus;
use App\Domain\StockFlow\ValueObjects\CashRegisterStatus;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderStatus;
use App\Domain\StockFlow\ValueObjects\StockMovementId;
use App\Domain\StockFlow\ValueObjects\AuditId;
use App\Domain\StockFlow\ValueObjects\CashRegisterId;
use App\Domain\StockFlow\ValueObjects\PhysicalCountId;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    // --- Item Entity ---

    public function test_item_create_returns_new_item(): void
    {
        $item = Item::create(
            companyId: CompanyId::fromInt(1),
            name: 'Test Item',
            unitPrice: Money::fromFloat(25.00),
            systemQuantity: 100,
        );

        $this->assertEquals(0, $item->id());
        $this->assertEquals('Test Item', $item->getName());
        $this->assertEquals(100, $item->getSystemQuantity());
        $this->assertEquals(25.00, $item->getUnitPrice()->toFloat());
        $this->assertFalse($item->isLowStock());
        $this->assertFalse($item->isOutOfStock());
        $this->assertTrue($item->hasSufficientStock(50));
        $this->assertFalse($item->hasSufficientStock(150));
    }

    public function test_item_create_with_department_and_bin(): void
    {
        $item = Item::create(
            companyId: CompanyId::fromInt(1),
            departmentId: DepartmentId::fromInt(5),
            binId: BinId::fromInt(10),
            categoryId: CategoryId::fromInt(3),
            name: 'Binned Item',
            sku: 'SKU-001',
            barcode: '123456789',
            brand: 'TestBrand',
            unitPrice: Money::fromFloat(10.00),
            systemQuantity: 50,
            reorderLevel: 10,
        );

        $this->assertEquals(5, $item->getDepartmentIdValue());
        $this->assertEquals(10, $item->getBinIdValue());
        $this->assertEquals(3, $item->getCategoryId()?->toInt());
        $this->assertEquals('SKU-001', $item->getSku());
        $this->assertEquals('123456789', $item->getBarcode());
        $this->assertEquals('TestBrand', $item->getBrand());
        $this->assertEquals(10, $item->getReorderLevel());
    }

    public function test_item_adjust_stock_increases(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 50);
        $item->adjustStock(25);
        $this->assertEquals(75, $item->getSystemQuantity());
    }

    public function test_item_adjust_stock_decreases(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 50);
        $item->adjustStock(-30);
        $this->assertEquals(20, $item->getSystemQuantity());
    }

    public function test_item_adjust_stock_does_not_go_below_zero(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 10);
        $item->adjustStock(-100);
        $this->assertEquals(0, $item->getSystemQuantity());
    }

    public function test_item_set_stock(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 50);
        $item->setStock(200);
        $this->assertEquals(200, $item->getSystemQuantity());
    }

    public function test_item_set_stock_does_not_go_below_zero(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 50);
        $item->setStock(-10);
        $this->assertEquals(0, $item->getSystemQuantity());
    }

    public function test_item_is_low_stock_when_below_reorder_level(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 5, reorderLevel: 10);
        $this->assertTrue($item->isLowStock());
    }

    public function test_item_is_not_low_stock_when_above_reorder_level(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 15, reorderLevel: 10);
        $this->assertFalse($item->isLowStock());
    }

    public function test_item_is_not_low_stock_when_no_reorder_level(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 5);
        $this->assertFalse($item->isLowStock());
    }

    public function test_item_is_out_of_stock_when_zero(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::zero(), systemQuantity: 0);
        $this->assertTrue($item->isOutOfStock());
    }

    public function test_item_get_stock_value(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Test', unitPrice: Money::fromFloat(10.00), systemQuantity: 5);
        $value = $item->getStockValue();
        $this->assertEquals(50.00, $value->toFloat());
    }

    public function test_item_update_details(): void
    {
        $item = Item::create(CompanyId::fromInt(1), name: 'Old', unitPrice: Money::fromFloat(10.00));
        $item->updateDetails(
            name: 'Updated',
            departmentId: DepartmentId::fromInt(2),
            binId: null,
            categoryId: null,
            sku: 'NEW-SKU',
            barcode: null,
            brand: 'NewBrand',
            description: 'Updated description',
            unitPrice: Money::fromFloat(15.00),
            wholesalePrice: Money::fromFloat(12.00),
            vipPrice: null,
            unit: 'pcs',
            reorderLevel: 5,
            category: 'New Category',
            notes: 'Updated notes',
        );

        $this->assertEquals('Updated', $item->getName());
        $this->assertEquals('NEW-SKU', $item->getSku());
        $this->assertEquals('NewBrand', $item->getBrand());
        $this->assertEquals(15.00, $item->getUnitPrice()->toFloat());
        $this->assertEquals(12.00, $item->getWholesalePrice()?->toFloat());
    }

    public function test_item_reconstitute_restores_state(): void
    {
        $now = new \DateTimeImmutable();
        $item = Item::reconstitute(
            id: ItemId::fromInt(42),
            companyId: CompanyId::fromInt(1),
            departmentId: DepartmentId::fromInt(3),
            binId: null,
            categoryId: null,
            name: 'Restored Item',
            sku: 'RST-001',
            barcode: null,
            brand: null,
            description: 'Restored from DB',
            unitPrice: Money::fromFloat(99.99),
            wholesalePrice: null,
            vipPrice: null,
            unit: 'kg',
            systemQuantity: 500,
            reorderLevel: 50,
            category: 'Restored',
            isExpirable: true,
            expiryDate: $now->modify('+30 days'),
            notes: 'Test notes',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(42, $item->id());
        $this->assertEquals(1, $item->getCompanyId()->toInt());
        $this->assertEquals(3, $item->getDepartmentIdValue());
        $this->assertEquals('Restored Item', $item->getName());
        $this->assertEquals(99.99, $item->getUnitPrice()->toFloat());
        $this->assertEquals(500, $item->getSystemQuantity());
        $this->assertTrue($item->isExpirable());
    }

    public function test_item_to_array_includes_all_fields(): void
    {
        $item = Item::create(
            companyId: CompanyId::fromInt(1),
            name: 'Array Item',
            unitPrice: Money::fromFloat(50.00),
            systemQuantity: 100,
        );

        $arr = $item->toArray();
        $this->assertEquals('Array Item', $arr['name']);
        $this->assertEquals(1, $arr['sa_company_id']);
        $this->assertEquals(50.00, $arr['unit_price']);
        $this->assertEquals(100, $arr['system_quantity']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }

    // --- Sale Entity ---

    public function test_sale_create(): void
    {
        $now = new \DateTimeImmutable();
        $sale = Sale::create(
            companyId: CompanyId::fromInt(1),
            receiptNumber: 'REC-001',
            saleDate: $now,
            saleTime: $now->format('H:i'),
            paymentMethod: PaymentMethod::cash(),
            subtotal: Money::fromFloat(100.00),
            discount: Money::fromFloat(10.00),
            tax: Money::fromFloat(5.00),
            total: Money::fromFloat(95.00),
            amountTendered: Money::fromFloat(100.00),
            changeDue: Money::fromFloat(5.00),
            soldBy: 1,
            notes: 'Test sale',
            currency: 'USD',
        );

        $this->assertEquals(0, $sale->id());
        $this->assertEquals('REC-001', $sale->getReceiptNumber());
        $this->assertEquals(95.00, $sale->getTotal()->toFloat());
        $this->assertTrue($sale->isCashPayment());
        $this->assertEquals(1, $sale->getSoldBy());
    }

    public function test_sale_add_item(): void
    {
        $now = new \DateTimeImmutable();
        $sale = Sale::create(
            companyId: CompanyId::fromInt(1),
            receiptNumber: 'REC-002',
            saleDate: $now,
            saleTime: $now->format('H:i'),
            paymentMethod: PaymentMethod::mobileMoney(),
            subtotal: Money::fromFloat(50.00),
            discount: Money::zero(),
            tax: Money::zero(),
            total: Money::fromFloat(50.00),
            amountTendered: Money::fromFloat(50.00),
            changeDue: Money::zero(),
            soldBy: 1,
        );

        $item = SaleItem::create(
            saleId: SaleId::fromInt(0),
            itemId: ItemId::fromInt(1),
            lotId: null,
            itemName: 'Product A',
            quantity: 2,
            unitPrice: Money::fromFloat(25.00),
        );

        $sale->addItem($item);
        $this->assertCount(1, $sale->getItems());
        $this->assertEquals('Product A', $sale->getItems()[0]->getItemName());
    }

    public function test_sale_is_cash_payment(): void
    {
        $now = new \DateTimeImmutable();
        $cashSale = Sale::create(CompanyId::fromInt(1), 'C001', $now, '10:00', PaymentMethod::cash(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), 1);
        $cardSale = Sale::create(CompanyId::fromInt(1), 'C002', $now, '10:00', PaymentMethod::card(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), Money::zero(), 1);

        $this->assertTrue($cashSale->isCashPayment());
        $this->assertFalse($cardSale->isCashPayment());
    }

    public function test_sale_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $sale = Sale::reconstitute(
            id: SaleId::fromInt(99),
            companyId: CompanyId::fromInt(1),
            receiptNumber: 'REC-099',
            saleDate: $now,
            saleTime: '14:30',
            paymentMethod: PaymentMethod::credit(),
            subtotal: Money::fromFloat(200.00),
            discount: Money::fromFloat(20.00),
            tax: Money::fromFloat(10.00),
            total: Money::fromFloat(190.00),
            amountTendered: Money::fromFloat(190.00),
            changeDue: Money::zero(),
            soldBy: 2,
            notes: 'Credit sale',
            currency: 'USD',
            exchangeRate: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(99, $sale->id());
        $this->assertEquals(190.00, $sale->getTotal()->toFloat());
        $this->assertEquals(2, $sale->getSoldBy());
        $this->assertFalse($sale->isCashPayment());
    }

    // --- StockMovement Entity ---

    public function test_stock_movement_create(): void
    {
        $m = StockMovement::create(
            companyId: CompanyId::fromInt(1),
            itemId: ItemId::fromInt(1),
            binId: null,
            type: MovementType::purchaseIn(),
            quantity: 100,
            unitPrice: Money::fromFloat(10.00),
            quantityBefore: 0,
            quantityAfter: 100,
            reason: 'Initial stock',
            referenceType: null,
            referenceId: null,
            createdBy: 1,
        );

        $this->assertEquals(0, $m->id());
        $this->assertEquals(100, $m->getQuantity());
        $this->assertEquals(1000.00, $m->getTotalValue()->toFloat());
        $this->assertEquals(0, $m->getQuantityBefore());
        $this->assertEquals(100, $m->getQuantityAfter());
        $this->assertTrue($m->getType()->equals(MovementType::purchaseIn()));
    }

    public function test_stock_movement_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $m = StockMovement::reconstitute(
            id: StockMovementId::fromInt(55),
            companyId: CompanyId::fromInt(1),
            itemId: ItemId::fromInt(2),
            binId: BinId::fromInt(3),
            type: MovementType::saleOut(),
            quantity: -5,
            unitPrice: Money::fromFloat(20.00),
            totalValue: Money::fromFloat(100.00),
            quantityBefore: 50,
            quantityAfter: 45,
            reason: 'Sale REC-001',
            referenceType: 'sale',
            referenceId: 1,
            createdBy: 1,
            createdAt: $now,
            itemName: 'Product X',
        );

        $this->assertEquals(55, $m->id());
        $this->assertEquals(-5, $m->getQuantity());
        $this->assertEquals(100.00, $m->getTotalValue()->toFloat());
        $this->assertEquals(45, $m->getQuantityAfter());
        $this->assertEquals('sale', $m->getReferenceType());
        $this->assertEquals('Product X', $m->getItemName());
    }

    public function test_stock_movement_total_value_is_abs_quantity_times_price(): void
    {
        $m = StockMovement::create(
            companyId: CompanyId::fromInt(1),
            itemId: ItemId::fromInt(1),
            binId: null,
            type: MovementType::saleOut(),
            quantity: -25,
            unitPrice: Money::fromFloat(30.00),
            quantityBefore: 100,
            quantityAfter: 75,
            reason: 'Sale',
            createdBy: 1,
        );

        $this->assertEquals(750.00, $m->getTotalValue()->toFloat());
    }

    // --- Audit Entity ---

    public function test_audit_create_with_variance(): void
    {
        $audit = Audit::create(
            companyId: CompanyId::fromInt(1),
            title: 'Monthly Audit',
            auditDate: new \DateTimeImmutable(),
            totalSystemValue: Money::fromFloat(10000.00),
            totalPhysicalValue: Money::fromFloat(9500.00),
            preparedFor: 'Management',
            preparedBy: 'Auditor',
            reportReference: 'AUD-001',
        );

        $this->assertEquals(0, $audit->id());
        $this->assertEquals('Monthly Audit', $audit->getTitle());
        $this->assertEquals('AUD-001', $audit->getReportReference());
        $this->assertEquals(10000.00, $audit->getTotalSystemValue()->toFloat());
        $this->assertEquals(9500.00, $audit->getTotalPhysicalValue()->toFloat());
        // Variance = system - physical = 10000 - 9500 = 500
        $this->assertEquals(500.00, $audit->getTotalVariance()->toFloat());
        $this->assertTrue($audit->getStatus()->isDraft());
    }

    public function test_audit_no_variance_when_equal(): void
    {
        $audit = Audit::create(
            companyId: CompanyId::fromInt(1),
            title: 'Perfect Audit',
            auditDate: new \DateTimeImmutable(),
            totalSystemValue: Money::fromFloat(5000.00),
            totalPhysicalValue: Money::fromFloat(5000.00),
        );

        $this->assertEquals(0, $audit->getTotalVariance()->toFloat());
        $this->assertTrue($audit->getUnaccountedValue()->isZero());
    }

    public function test_audit_finalize_computes_unaccounted_value(): void
    {
        // System=10000, Physical=9500, Variance=500
        $audit = Audit::create(
            companyId: CompanyId::fromInt(1),
            title: 'Final Audit',
            auditDate: new \DateTimeImmutable(),
            totalSystemValue: Money::fromFloat(10000.00),
            totalPhysicalValue: Money::fromFloat(9500.00),
        );

        // Recorded sales between counts = 300
        $audit->finalize(
            totalRecordedSales: Money::fromFloat(300.00),
            executiveSummary: 'Completed',
            recommendations: 'Improve tracking',
            conclusion: 'Satisfactory',
        );

        $this->assertTrue($audit->getStatus()->isFinalized());
        $this->assertEquals(300.00, $audit->getTotalRecordedSales()->toFloat());
        // unaccountedValue = variance - recordedSales = 500 - 300 = 200
        $this->assertEquals(200.00, $audit->getUnaccountedValue()->toFloat());
        $this->assertEquals('Completed', $audit->getExecutiveSummary());
    }

    public function test_audit_finalize_with_no_recorded_sales(): void
    {
        $audit = Audit::create(
            companyId: CompanyId::fromInt(1),
            title: 'Zero Sales Audit',
            auditDate: new \DateTimeImmutable(),
            totalSystemValue: Money::fromFloat(1000.00),
            totalPhysicalValue: Money::fromFloat(800.00),
        );

        $audit->finalize(totalRecordedSales: Money::zero());
        // unaccountedValue = 200 - 0 = 200
        $this->assertEquals(200.00, $audit->getUnaccountedValue()->toFloat());
    }

    public function test_audit_add_item(): void
    {
        $audit = Audit::create(
            companyId: CompanyId::fromInt(1),
            title: 'Audit With Items',
            auditDate: new \DateTimeImmutable(),
            totalSystemValue: Money::zero(),
            totalPhysicalValue: Money::zero(),
        );

        $auditItem = AuditItem::create(
            auditId: AuditId::fromInt(0),
            itemId: ItemId::fromInt(1),
            binId: null,
            itemName: 'Product A',
            unitPrice: Money::fromFloat(10.00),
            systemQty: 100,
            physicalQty: 95,
        );

        $audit->addItem($auditItem);
        $this->assertCount(1, $audit->getItems());
        $this->assertEquals(100, $audit->getItems()[0]->getSystemQty());
    }

    public function test_audit_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $audit = Audit::reconstitute(
            id: AuditId::fromInt(77),
            companyId: CompanyId::fromInt(1),
            title: 'Q1 Audit',
            reportReference: 'AUD-077',
            auditDate: $now,
            status: AuditStatus::finalized(),
            totalSystemValue: Money::fromFloat(20000.00),
            totalPhysicalValue: Money::fromFloat(19500.00),
            totalVariance: Money::fromFloat(500.00),
            unaccountedValue: Money::fromFloat(200.00),
            totalRecordedSales: Money::fromFloat(300.00),
            executiveSummary: 'Done',
            recommendations: 'None',
            conclusion: 'Pass',
            preparedFor: 'Board',
            preparedBy: 'Auditor',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(77, $audit->id());
        $this->assertTrue($audit->getStatus()->isFinalized());
        $this->assertEquals(200.00, $audit->getUnaccountedValue()->toFloat());
    }

    // --- CashRegister Entity ---

    public function test_cash_register_create(): void
    {
        $register = CashRegister::create(
            companyId: CompanyId::fromInt(1),
            registerDate: new \DateTimeImmutable(),
            openingBalance: Money::fromFloat(500.00),
            openedBy: 1,
        );

        $this->assertEquals(0, $register->id());
        $this->assertEquals(500.00, $register->getOpeningBalance()->toFloat());
        $this->assertTrue($register->isOpen());
        $this->assertEquals(500.00, $register->getExpectedClosing()->toFloat());
    }

    public function test_cash_register_record_sale(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(500.00), 1);
        $register->recordSale(Money::fromFloat(200.00));

        $this->assertEquals(200.00, $register->getTotalSales()->toFloat());
        // expected = opening(500) + sales(200) - expenses(0) - banking(0) = 700
        $this->assertEquals(700.00, $register->getExpectedClosing()->toFloat());
    }

    public function test_cash_register_add_expense(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(1000.00), 1);
        $register->addExpense(Money::fromFloat(150.00));

        $this->assertEquals(150.00, $register->getTotalExpenses()->toFloat());
        // expected = 1000 - 150 = 850
        $this->assertEquals(850.00, $register->getExpectedClosing()->toFloat());
    }

    public function test_cash_register_add_banking(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(1000.00), 1);
        $register->recordSale(Money::fromFloat(500.00));
        $register->addBanking(Money::fromFloat(300.00));

        $this->assertEquals(300.00, $register->getTotalBanking()->toFloat());
        // expected = 1000 + 500 - 0 - 300 = 1200
        $this->assertEquals(1200.00, $register->getExpectedClosing()->toFloat());
    }

    public function test_cash_register_close_with_zero_variance(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(500.00), 1);
        $register->recordSale(Money::fromFloat(200.00));
        // expected = 700
        $register->close(Money::fromFloat(700.00), 'All good', 1);

        $this->assertFalse($register->isOpen());
        $this->assertEquals(700.00, $register->getActualClosing()?->toFloat());
        $this->assertEquals(0.00, $register->getVariance()?->toFloat());
        $this->assertEquals(1, $register->getClosedBy());
    }

    public function test_cash_register_close_with_variance(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(500.00), 1);
        $register->recordSale(Money::fromFloat(200.00));
        // expected = 700, actual = 690
        $register->close(Money::fromFloat(690.00), 'Short by 10', 1);

        $this->assertEquals(-10.00, $register->getVariance()?->toFloat());
    }

    public function test_cash_register_verify(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(500.00), 1);
        $register->close(Money::fromFloat(500.00), 'Closed', 1);
        $register->verify();

        $this->assertFalse($register->isOpen());
        $this->assertFalse($register->getStatus()->isClosed());
        $this->assertEquals('verified', $register->getStatus()->value());
    }

    public function test_cash_register_multiple_operations(): void
    {
        $register = CashRegister::create(CompanyId::fromInt(1), new \DateTimeImmutable(), Money::fromFloat(1000.00), 1);

        $register->recordSale(Money::fromFloat(1500.00));
        $register->recordSale(Money::fromFloat(750.00));
        $register->addExpense(Money::fromFloat(200.00));
        $register->addExpense(Money::fromFloat(50.00));
        $register->addBanking(Money::fromFloat(1000.00));

        // expected = 1000 + 2250 - 250 - 1000 = 2000
        $this->assertEquals(2250.00, $register->getTotalSales()->toFloat());
        $this->assertEquals(250.00, $register->getTotalExpenses()->toFloat());
        $this->assertEquals(1000.00, $register->getTotalBanking()->toFloat());
        $this->assertEquals(2000.00, $register->getExpectedClosing()->toFloat());

        $register->close(Money::fromFloat(2000.00), 'Perfect', 1);
        $this->assertEquals(0.00, $register->getVariance()?->toFloat());
    }

    public function test_cash_register_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $register = CashRegister::reconstitute(
            id: CashRegisterId::fromInt(33),
            companyId: CompanyId::fromInt(1),
            registerDate: $now,
            status: CashRegisterStatus::verified(),
            openingBalance: Money::fromFloat(500.00),
            totalSales: Money::fromFloat(2000.00),
            totalExpenses: Money::fromFloat(300.00),
            totalBanking: Money::fromFloat(1000.00),
            expectedClosing: Money::fromFloat(1200.00),
            actualClosing: Money::fromFloat(1200.00),
            variance: Money::zero(),
            notes: 'Verified',
            openedBy: 1,
            closedBy: 1,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(33, $register->id());
        $this->assertEquals('verified', $register->getStatus()->value());
        $this->assertEquals(1200.00, $register->getExpectedClosing()->toFloat());
    }

    // --- PhysicalCount Entity ---

    public function test_physical_count_create(): void
    {
        $count = PhysicalCount::create(
            companyId: CompanyId::fromInt(1),
            title: 'Year-End Count',
            countDate: new \DateTimeImmutable(),
            countedBy: 1,
            notes: 'Full inventory',
        );

        $this->assertEquals(0, $count->id());
        $this->assertEquals('Year-End Count', $count->getTitle());
        $this->assertTrue($count->isDraft());
        $this->assertFalse($count->isCompleted());
    }

    public function test_physical_count_complete(): void
    {
        $count = PhysicalCount::create(CompanyId::fromInt(1), 'Count', new \DateTimeImmutable(), 1);
        $count->complete(2);

        $this->assertTrue($count->isCompleted());
        $this->assertFalse($count->isDraft());
        $this->assertEquals(2, $count->getVerifiedBy());
    }

    public function test_physical_count_add_item(): void
    {
        $count = PhysicalCount::create(CompanyId::fromInt(1), 'Count', new \DateTimeImmutable(), 1);

        $countItem = CountItem::create(
            physicalCountId: PhysicalCountId::fromInt(0),
            itemId: ItemId::fromInt(1),
            binId: null,
            systemQuantity: 100,
            physicalQuantity: 0,
            unitPrice: Money::fromFloat(10.00),
            itemName: 'Item A',
        );
        $count->addItem($countItem);

        $this->assertCount(1, $count->getItems());
    }

    public function test_physical_count_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $count = PhysicalCount::reconstitute(
            id: PhysicalCountId::fromInt(66),
            companyId: CompanyId::fromInt(1),
            title: 'Reconstituted Count',
            countDate: $now,
            status: 'completed',
            countedBy: 1,
            verifiedBy: 2,
            notes: 'Done',
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(66, $count->id());
        $this->assertTrue($count->isCompleted());
        $this->assertEquals(2, $count->getVerifiedBy());
    }

    // --- PurchaseOrder Entity ---

    public function test_purchase_order_create(): void
    {
        $po = PurchaseOrder::create(
            companyId: CompanyId::fromInt(1),
            supplierId: SupplierId::fromInt(5),
            orderNumber: 'PO-001',
            orderDate: new \DateTimeImmutable(),
            subtotal: Money::fromFloat(5000.00),
            notes: 'Bulk order',
            currency: 'USD',
        );

        $this->assertEquals(0, $po->id());
        $this->assertEquals('PO-001', $po->getOrderNumber());
        $this->assertEquals(5000.00, $po->getTotal()->toFloat());
        $this->assertTrue($po->getStatus()->isOpen());
        $this->assertFalse($po->getStatus()->isReceived());
        $this->assertEquals(5, $po->getSupplierId()?->toInt());
    }

    public function test_purchase_order_receive(): void
    {
        $po = PurchaseOrder::create(CompanyId::fromInt(1), null, 'PO-002', new \DateTimeImmutable(), Money::fromFloat(1000.00));
        $po->receive();

        $this->assertTrue($po->getStatus()->isReceived());
        $this->assertFalse($po->getStatus()->isOpen());
    }

    public function test_purchase_order_mark_partial(): void
    {
        $po = PurchaseOrder::create(CompanyId::fromInt(1), null, 'PO-003', new \DateTimeImmutable(), Money::fromFloat(1000.00));
        $po->markPartial();

        $this->assertTrue($po->getStatus()->isOpen());
        $this->assertEquals('partial', $po->getStatus()->value());
    }

    public function test_purchase_order_add_item(): void
    {
        $po = PurchaseOrder::create(CompanyId::fromInt(1), null, 'PO-004', new \DateTimeImmutable(), Money::zero());

        $item = PurchaseOrderItem::create(
            purchaseOrderId: PurchaseOrderId::fromInt(0),
            itemId: ItemId::fromInt(1),
            lotId: null,
            quantityOrdered: 100,
            quantityReceived: 0,
            unitCost: Money::fromFloat(25.00),
            itemName: 'Widget',
        );

        $po->addItem($item);
        $this->assertCount(1, $po->getItems());
    }

    public function test_purchase_order_set_supplier_name(): void
    {
        $po = PurchaseOrder::create(CompanyId::fromInt(1), null, 'PO-005', new \DateTimeImmutable(), Money::zero());
        $po->setSupplierName('ACME Corp');
        $this->assertEquals('ACME Corp', $po->getSupplierName());
    }

    public function test_purchase_order_reconstitute(): void
    {
        $now = new \DateTimeImmutable();
        $po = PurchaseOrder::reconstitute(
            id: PurchaseOrderId::fromInt(88),
            companyId: CompanyId::fromInt(1),
            supplierId: null,
            orderNumber: 'PO-088',
            orderDate: $now,
            status: PurchaseOrderStatus::received(),
            subtotal: Money::fromFloat(3000.00),
            tax: Money::fromFloat(300.00),
            total: Money::fromFloat(3300.00),
            notes: 'Received in full',
            currency: 'USD',
            exchangeRate: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(88, $po->id());
        $this->assertEquals('PO-088', $po->getOrderNumber());
        $this->assertTrue($po->getStatus()->isReceived());
        $this->assertEquals(3300.00, $po->getTotal()->toFloat());
    }

    // --- CountItem Entity ---

    public function test_count_item_create(): void
    {
        $ci = CountItem::create(
            physicalCountId: PhysicalCountId::fromInt(1),
            itemId: ItemId::fromInt(1),
            binId: null,
            systemQuantity: 50,
            physicalQuantity: 45,
            unitPrice: Money::fromFloat(10.00),
            itemName: 'Test Item',
        );

        $this->assertEquals(0, $ci->id());
        $this->assertEquals(50, $ci->getSystemQuantity());
        $this->assertEquals(45, $ci->getPhysicalQuantity());
        // variance = physical - system = -5
        $this->assertEquals(-5, $ci->getVariance());
        // variance_value = -5 * 10 = -50
        $this->assertEquals(-50.00, $ci->getVarianceValue()->toFloat());
    }

    public function test_count_item_record_physical_recalculates_variance(): void
    {
        $ci = CountItem::create(
            physicalCountId: PhysicalCountId::fromInt(1),
            itemId: ItemId::fromInt(1),
            binId: null,
            systemQuantity: 100,
            physicalQuantity: 0,
            unitPrice: Money::fromFloat(20.00),
        );

        $ci->recordPhysical(90);
        $this->assertEquals(90, $ci->getPhysicalQuantity());
        // variance = 90 - 100 = -10
        $this->assertEquals(-10, $ci->getVariance());
        $this->assertEquals(-200.00, $ci->getVarianceValue()->toFloat());
    }

    // --- SaleItem Entity ---

    public function test_sale_item_create(): void
    {
        $si = SaleItem::create(
            saleId: SaleId::fromInt(1),
            itemId: ItemId::fromInt(1),
            lotId: null,
            itemName: 'Product',
            quantity: 3,
            unitPrice: Money::fromFloat(15.00),
        );

        $this->assertEquals(0, $si->id());
        $this->assertEquals('Product', $si->getItemName());
        $this->assertEquals(3, $si->getQuantity());
        $this->assertEquals(45.00, $si->getTotal()->toFloat());
    }

    // --- PurchaseOrderItem Entity ---

    public function test_purchase_order_item_create(): void
    {
        $poi = PurchaseOrderItem::create(
            purchaseOrderId: PurchaseOrderId::fromInt(1),
            itemId: ItemId::fromInt(1),
            lotId: null,
            quantityOrdered: 50,
            quantityReceived: 0,
            unitCost: Money::fromFloat(12.00),
            itemName: 'Raw Material',
        );

        $this->assertEquals(0, $poi->id());
        $this->assertEquals(50, $poi->getQuantityOrdered());
        $this->assertEquals(0, $poi->getQuantityReceived());
        $this->assertFalse($poi->isFullyReceived());
    }

    public function test_purchase_order_item_receive(): void
    {
        $poi = PurchaseOrderItem::create(
            purchaseOrderId: PurchaseOrderId::fromInt(1),
            itemId: ItemId::fromInt(1),
            lotId: null,
            quantityOrdered: 50,
            quantityReceived: 0,
            unitCost: Money::fromFloat(12.00),
        );

        $poi->receive(50);
        $this->assertEquals(50, $poi->getQuantityReceived());
        $this->assertTrue($poi->isFullyReceived());

        // total cost = 50 * 12 = 600
        $this->assertEquals(600.00, $poi->getTotalCost()->toFloat());
    }

    public function test_purchase_order_item_partial_receive(): void
    {
        $poi = PurchaseOrderItem::create(
            purchaseOrderId: PurchaseOrderId::fromInt(1),
            itemId: ItemId::fromInt(1),
            lotId: null,
            quantityOrdered: 100,
            quantityReceived: 0,
            unitCost: Money::fromFloat(10.00),
        );

        $poi->receive(40);
        $this->assertEquals(40, $poi->getQuantityReceived());
        $this->assertFalse($poi->isFullyReceived());
    }

    // --- AuditItem Entity ---

    public function test_audit_item_create(): void
    {
        $ai = AuditItem::create(
            auditId: AuditId::fromInt(1),
            itemId: ItemId::fromInt(1),
            binId: null,
            itemName: 'Audited Item',
            unitPrice: Money::fromFloat(50.00),
            systemQty: 200,
            physicalQty: 190,
        );

        $this->assertEquals(0, $ai->id());
        $this->assertEquals(200, $ai->getSystemQty());
        $this->assertEquals(190, $ai->getPhysicalQty());
        // gap = system - physical = 10
        $this->assertEquals(10, $ai->getGapQty());
        // system_value = 200 * 50 = 10000
        $this->assertEquals(10000.00, $ai->getSystemValue()->toFloat());
        // physical_value = 190 * 50 = 9500
        $this->assertEquals(9500.00, $ai->getPhysicalValue()->toFloat());
        // gap_value = 10 * 50 = 500
        $this->assertEquals(500.00, $ai->getGapValue()->toFloat());
    }

    // --- toArray consistency ---

    public function test_item_to_array_roundtrip(): void
    {
        $original = Item::create(
            companyId: CompanyId::fromInt(1),
            name: 'Roundtrip',
            unitPrice: Money::fromFloat(25.00),
            systemQuantity: 100,
            sku: 'RTP-001',
        );

        $arr = $original->toArray();
        $this->assertEquals('Roundtrip', $arr['name']);
        $this->assertEquals(25.00, $arr['unit_price']);
        $this->assertEquals(100, $arr['system_quantity']);
        $this->assertEquals(1, $arr['sa_company_id']);
    }

    public function test_sale_to_array_roundtrip(): void
    {
        $now = new \DateTimeImmutable();
        $sale = Sale::create(
            companyId: CompanyId::fromInt(1),
            receiptNumber: 'RTP-001',
            saleDate: $now,
            saleTime: '10:00',
            paymentMethod: PaymentMethod::cash(),
            subtotal: Money::fromFloat(100.00),
            discount: Money::zero(),
            tax: Money::fromFloat(10.00),
            total: Money::fromFloat(110.00),
            amountTendered: Money::fromFloat(150.00),
            changeDue: Money::fromFloat(40.00),
            soldBy: 1,
        );

        $arr = $sale->toArray();
        $this->assertEquals('RTP-001', $arr['receipt_number']);
        $this->assertEquals(110.00, $arr['total']);
        $this->assertEquals(150.00, $arr['amount_tendered']);
        $this->assertEquals('cash', $arr['payment_method']);
    }
}
