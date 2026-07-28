<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Order;
use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\OrderStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class OrderEntityTest extends TestCase
{
    public function test_create(): void
    {
        $order = Order::create(
            siteId: 1,
            customerName: 'John Doe',
            customerPhone: '+260977123456',
            items: [
                ['name' => 'Product A', 'quantity' => 2, 'price' => 5000],
            ],
            subtotalInNgwee: 10000,
            shippingCostInNgwee: 500,
        );

        $this->assertNull($order->getId());
        $this->assertEquals(1, $order->getSiteId());
        $this->assertEquals('John Doe', $order->getCustomerName());
        $this->assertEquals('+260977123456', $order->getCustomerPhone());
        $this->assertEquals(10000, $order->getSubtotal()->getAmountInNgwee());
        $this->assertEquals(500, $order->getShippingCost()->getAmountInNgwee());
        $this->assertEquals(10500, $order->getTotal()->getAmountInNgwee());
        $this->assertTrue($order->getStatus()->isPending());
        $this->assertNull($order->getCustomerEmail());
        $this->assertNull($order->getCustomerAddress());
        $this->assertNull($order->getCustomerCity());
        $this->assertNull($order->getPaymentMethod());
        $this->assertNull($order->getPaymentReference());
        $this->assertNull($order->getNotes());
        $this->assertNull($order->getAdminNotes());
        $this->assertNull($order->getPaidAt());
        $this->assertNull($order->getShippedAt());
        $this->assertNull($order->getDeliveredAt());
        $this->assertEquals(0, $order->getDiscountAmount()->getAmountInNgwee());
        $this->assertNull($order->getDiscountCode());
        $this->assertStringStartsWith('GB-', $order->getOrderNumber());
    }

    public function test_create_with_email_and_address(): void
    {
        $order = Order::create(
            siteId: 1,
            customerName: 'Jane',
            customerPhone: '+260977654321',
            items: [['name' => 'Item', 'quantity' => 1, 'price' => 3000]],
            subtotalInNgwee: 3000,
            shippingCostInNgwee: 0,
            customerEmail: 'jane@example.com',
            customerAddress: 'Lusaka',
        );

        $this->assertEquals('jane@example.com', $order->getCustomerEmail());
        $this->assertEquals('Lusaka', $order->getCustomerAddress());
        $this->assertNull($order->getCustomerCity());
    }

    public function test_total_is_subtotal_plus_shipping(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 8000, 2000);
        $this->assertEquals(10000, $order->getTotal()->getAmountInNgwee());
    }

    public function test_zero_shipping_by_default(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 5000);
        $this->assertEquals(0, $order->getShippingCost()->getAmountInNgwee());
        $this->assertEquals(5000, $order->getTotal()->getAmountInNgwee());
    }

    public function test_reconstitute(): void
    {
        $now = new DateTimeImmutable();
        $order = Order::reconstitute(
            id: OrderId::fromInt(100),
            siteId: 1, orderNumber: 'GB-001',
            customerName: 'John', customerPhone: '+260977000000',
            customerEmail: 'john@test.com', customerAddress: 'Lusaka',
            customerCity: 'Lusaka',
            items: [['name' => 'A', 'quantity' => 1, 'price' => 1000]],
            subtotal: Money::fromNgwee(1000),
            shippingCost: Money::zero(),
            discountAmount: Money::zero(), discountCode: null,
            total: Money::fromNgwee(1000),
            status: OrderStatus::paid(),
            paymentMethod: 'momo', paymentReference: 'REF123',
            notes: null, adminNotes: 'Verified',
            paidAt: $now, shippedAt: null, deliveredAt: null,
            createdAt: $now, updatedAt: $now,
        );

        $this->assertEquals(100, $order->getId()->value());
        $this->assertEquals('GB-001', $order->getOrderNumber());
        $this->assertTrue($order->getStatus()->isPaid());
        $this->assertEquals('momo', $order->getPaymentMethod());
        $this->assertEquals('REF123', $order->getPaymentReference());
        $this->assertEquals('Verified', $order->getAdminNotes());
        $this->assertEquals('john@test.com', $order->getCustomerEmail());
        $this->assertEquals('Lusaka', $order->getCustomerCity());
    }

    public function test_apply_discount(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->applyDiscount('WELCOME10', 1000);

        $this->assertEquals('WELCOME10', $order->getDiscountCode());
        $this->assertEquals(1000, $order->getDiscountAmount()->getAmountInNgwee());
    }

    public function test_apply_discount_recalculates_total(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 500);
        $this->assertEquals(10500, $order->getTotal()->getAmountInNgwee());

        $order->applyDiscount('SAVE', 2000);
        $this->assertEquals(8500, $order->getTotal()->getAmountInNgwee());
    }

    public function test_set_payment_method(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->setPaymentMethod('airtel_money');

        $this->assertEquals('airtel_money', $order->getPaymentMethod());
        $this->assertTrue($order->getStatus()->isPaymentPending());
    }

    public function test_mark_as_paid(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('TXN-001');

        $this->assertTrue($order->getStatus()->isPaid());
        $this->assertEquals('TXN-001', $order->getPaymentReference());
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->getPaidAt());
    }

    public function test_mark_as_processing(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('REF');
        $order->markAsProcessing();
        $this->assertTrue($order->getStatus()->isProcessing());
    }

    public function test_mark_as_shipped(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('REF');
        $order->markAsProcessing();
        $order->markAsShipped();
        $this->assertTrue($order->getStatus()->isShipped());
        $this->assertNotNull($order->getShippedAt());
    }

    public function test_mark_as_delivered(): void
    {
        $order = $this->createFullLifecycleOrder();
        $order->markAsDelivered();
        $this->assertTrue($order->getStatus()->isDelivered());
        $this->assertNotNull($order->getDeliveredAt());
    }

    public function test_mark_as_completed(): void
    {
        $order = $this->createFullLifecycleOrder();
        $order->markAsDelivered();
        $order->markAsCompleted();
        $this->assertTrue($order->getStatus()->isCompleted());
    }

    public function test_cancel(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->cancel();
        $this->assertTrue($order->getStatus()->isCancelled());
    }

    public function test_cancel_when_payment_pending(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->setPaymentMethod('momo');
        $order->cancel();
        $this->assertTrue($order->getStatus()->isCancelled());
    }

    public function test_refund_after_paid(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('REF');
        $order->refund();
        $this->assertTrue($order->getStatus()->isRefunded());
    }

    public function test_can_be_cancelled_when_pending(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_can_be_cancelled_when_payment_pending(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->setPaymentMethod('momo');
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_cannot_be_cancelled_when_shipped(): void
    {
        $order = $this->createFullLifecycleOrder();
        $order->markAsShipped();
        $this->assertFalse($order->canBeCancelled());
    }

    public function test_add_note(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->addNote('Customer requested gift wrap');
        $this->assertEquals('Customer requested gift wrap', $order->getNotes());
    }

    public function test_add_admin_note(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->addAdminNote('Payment verified via manual check');
        $this->assertEquals('Payment verified via manual check', $order->getAdminNotes());
    }

    public function test_get_item_count(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [
            ['name' => 'A', 'quantity' => 2],
            ['name' => 'B', 'quantity' => 3],
        ], 10000, 0);
        $this->assertEquals(5, $order->getItemCount());
    }

    public function test_get_item_count_with_empty_items(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 0, 0);
        $this->assertEquals(0, $order->getItemCount());
    }

    public function test_is_paid(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $this->assertFalse($order->isPaid());

        $order->markAsPaid('REF');
        $this->assertTrue($order->isPaid());
    }

    public function test_order_number_starts_with_gb(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $this->assertStringStartsWith('GB-', $order->getOrderNumber());
    }

    public function test_sequential_full_lifecycle(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);

        $order->markAsPaid('TXN');
        $this->assertTrue($order->getStatus()->isPaid());

        $order->markAsProcessing();
        $this->assertTrue($order->getStatus()->isProcessing());

        $order->markAsShipped();
        $this->assertTrue($order->getStatus()->isShipped());

        $order->markAsDelivered();
        $this->assertTrue($order->getStatus()->isDelivered());

        $order->markAsCompleted();
        $this->assertTrue($order->getStatus()->isCompleted());
    }

    public function test_cannot_mark_as_paid_after_refund(): void
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('REF');
        $order->refund();
        $this->assertTrue($order->getStatus()->isRefunded());
    }

    private function createFullLifecycleOrder(): Order
    {
        $order = Order::create(1, 'John', '+260977000000', [], 10000, 0);
        $order->markAsPaid('REF');
        $order->markAsProcessing();
        return $order;
    }
}
