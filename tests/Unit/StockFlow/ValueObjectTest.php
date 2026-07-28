<?php

namespace Tests\Unit\StockFlow;

use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\MovementType;
use App\Domain\StockFlow\ValueObjects\AuditStatus;
use App\Domain\StockFlow\ValueObjects\CashRegisterStatus;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderStatus;
use App\Domain\StockFlow\ValueObjects\PaymentMethod;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    // --- Money ---

    public function test_money_from_float_creates_valid_money(): void
    {
        $m = Money::fromFloat(100.50);
        $this->assertEquals(100.50, $m->toFloat());
        $this->assertEquals('MWK', $m->currency());
    }

    public function test_money_allows_negative_amount(): void
    {
        $m = Money::fromFloat(-10);
        $this->assertEquals(-10.00, $m->toFloat());
    }

    public function test_money_zero_returns_zero(): void
    {
        $m = Money::zero();
        $this->assertTrue($m->isZero());
        $this->assertEquals(0.0, $m->toFloat());
    }

    public function test_money_rounds_to_two_decimals(): void
    {
        $m = Money::fromFloat(10.3456);
        $this->assertEquals(10.35, $m->toFloat());
    }

    public function test_money_add(): void
    {
        $a = Money::fromFloat(10.50);
        $b = Money::fromFloat(20.25);
        $result = $a->add($b);
        $this->assertEquals(30.75, $result->toFloat());
    }

    public function test_money_subtract(): void
    {
        $a = Money::fromFloat(50.00);
        $b = Money::fromFloat(30.00);
        $result = $a->subtract($b);
        $this->assertEquals(20.00, $result->toFloat());
    }

    public function test_money_subtract_allows_negative(): void
    {
        $a = Money::fromFloat(10.00);
        $b = Money::fromFloat(30.00);
        $result = $a->subtract($b);
        $this->assertEquals(-20.00, $result->toFloat());
    }

    public function test_money_multiply(): void
    {
        $m = Money::fromFloat(10.50);
        $result = $m->multiply(3);
        $this->assertEquals(31.50, $result->toFloat());
    }

    public function test_money_multiply_by_zero(): void
    {
        $m = Money::fromFloat(100.00);
        $result = $m->multiply(0);
        $this->assertTrue($result->isZero());
    }

    public function test_money_comparison(): void
    {
        $a = Money::fromFloat(50.00);
        $b = Money::fromFloat(100.00);
        $this->assertTrue($a->isLessThan($b));
        $this->assertTrue($b->isGreaterThan($a));
        $this->assertFalse($a->isGreaterThan($b));
    }

    public function test_money_equals(): void
    {
        $a = Money::fromFloat(10.00, 'USD');
        $b = Money::fromFloat(10.00, 'USD');
        $c = Money::fromFloat(10.00, 'MWK');
        $d = Money::fromFloat(20.00, 'USD');
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals($d));
    }

    public function test_money_string_representation(): void
    {
        $m = Money::fromFloat(100.50, 'USD');
        $this->assertEquals('100.50 USD', (string) $m);
    }

    public function test_money_custom_currency(): void
    {
        $m = Money::fromFloat(50, 'ZMW');
        $this->assertEquals('ZMW', $m->currency());
        $this->assertEquals(50.0, $m->toFloat());
    }

    // --- MovementType ---

    public function test_movement_type_constants(): void
    {
        $this->assertTrue(MovementType::purchaseIn()->isIncoming());
        $this->assertFalse(MovementType::saleOut()->isIncoming());
        $this->assertTrue(MovementType::saleOut()->isOutgoing());
        $this->assertTrue(MovementType::adjustmentIn()->isIncoming());
        $this->assertFalse(MovementType::adjustmentOut()->isIncoming());
        $this->assertTrue(MovementType::returnIn()->isIncoming());
        $this->assertTrue(MovementType::openingBalance()->isIncoming());
        $this->assertFalse(MovementType::damageOut()->isIncoming());
        $this->assertFalse(MovementType::expiredOut()->isIncoming());
        $this->assertFalse(MovementType::physicalCount()->isIncoming());
    }

    public function test_movement_type_from_string_valid(): void
    {
        $m = MovementType::fromString('purchase_in');
        $this->assertTrue($m->equals(MovementType::purchaseIn()));
    }

    public function test_movement_type_from_string_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MovementType::fromString('invalid_type');
    }

    public function test_movement_type_value_and_label(): void
    {
        $m = MovementType::saleOut();
        $this->assertEquals('sale_out', $m->value());
        $this->assertEquals('Sale out', $m->label());
    }

    public function test_movement_type_all_returns_all_types(): void
    {
        $all = MovementType::all();
        $this->assertContains('purchase_in', $all);
        $this->assertContains('sale_out', $all);
        $this->assertNotContains('transfer_out', $all);
        $this->assertCount(9, $all);
    }

    public function test_movement_type_transfer_types(): void
    {
        $this->assertTrue(MovementType::transferOut()->isOutgoing());
        $this->assertTrue(MovementType::transferIn()->isIncoming());
        $this->assertTrue(MovementType::transferCancelled()->isOutgoing());
    }

    // --- AuditStatus ---

    public function test_audit_status_values(): void
    {
        $this->assertFalse(AuditStatus::draft()->isFinalized());
        $this->assertTrue(AuditStatus::finalized()->isFinalized());
        $this->assertEquals('draft', AuditStatus::draft()->value());
        $this->assertEquals('Finalized', AuditStatus::finalized()->label());
    }

    public function test_audit_status_from_string(): void
    {
        $this->assertTrue(AuditStatus::fromString('draft')->equals(AuditStatus::draft()));
        $this->assertTrue(AuditStatus::fromString('finalized')->equals(AuditStatus::finalized()));
    }

    public function test_audit_status_from_string_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AuditStatus::fromString('invalid');
    }

    public function test_audit_status_all(): void
    {
        $this->assertCount(2, AuditStatus::all());
        $this->assertContains('draft', AuditStatus::all());
        $this->assertContains('finalized', AuditStatus::all());
    }

    // --- CashRegisterStatus ---

    public function test_cash_register_status(): void
    {
        $this->assertTrue(CashRegisterStatus::open()->isOpen());
        $this->assertFalse(CashRegisterStatus::closed()->isOpen());
        $this->assertTrue(CashRegisterStatus::closed()->isClosed());
        $this->assertFalse(CashRegisterStatus::verified()->isOpen());
        $this->assertFalse(CashRegisterStatus::verified()->isClosed());
    }

    public function test_cash_register_status_from_string_valid(): void
    {
        $this->assertTrue(CashRegisterStatus::fromString('verified')->equals(CashRegisterStatus::verified()));
    }

    public function test_cash_register_status_from_string_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CashRegisterStatus::fromString('invalid');
    }

    public function test_cash_register_status_all(): void
    {
        $this->assertCount(3, CashRegisterStatus::all());
    }

    // --- PurchaseOrderStatus ---

    public function test_purchase_order_status(): void
    {
        $this->assertTrue(PurchaseOrderStatus::draft()->isOpen());
        $this->assertTrue(PurchaseOrderStatus::ordered()->isOpen());
        $this->assertTrue(PurchaseOrderStatus::partial()->isOpen());
        $this->assertFalse(PurchaseOrderStatus::received()->isOpen());
        $this->assertFalse(PurchaseOrderStatus::cancelled()->isOpen());
        $this->assertTrue(PurchaseOrderStatus::received()->isReceived());
        $this->assertFalse(PurchaseOrderStatus::draft()->isReceived());
    }

    public function test_purchase_order_status_from_string(): void
    {
        $this->assertTrue(PurchaseOrderStatus::fromString('partial')->equals(PurchaseOrderStatus::partial()));
    }

    public function test_purchase_order_status_from_string_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PurchaseOrderStatus::fromString('invalid');
    }

    public function test_purchase_order_status_all(): void
    {
        $this->assertCount(5, PurchaseOrderStatus::all());
    }

    // --- PaymentMethod ---

    public function test_payment_method(): void
    {
        $this->assertTrue(PaymentMethod::cash()->isCash());
        $this->assertFalse(PaymentMethod::mobileMoney()->isCash());
        $this->assertFalse(PaymentMethod::card()->isCash());
        $this->assertFalse(PaymentMethod::credit()->isCash());
        $this->assertFalse(PaymentMethod::transfer()->isCash());
    }

    public function test_payment_method_from_string(): void
    {
        $this->assertTrue(PaymentMethod::fromString('mobile_money')->equals(PaymentMethod::mobileMoney()));
    }

    public function test_payment_method_from_string_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PaymentMethod::fromString('bitcoin');
    }

    public function test_payment_method_label(): void
    {
        $this->assertEquals('Mobile money', PaymentMethod::mobileMoney()->label());
        $this->assertEquals('Cash', PaymentMethod::cash()->label());
    }

    public function test_payment_method_all(): void
    {
        $this->assertCount(5, PaymentMethod::all());
    }
}
