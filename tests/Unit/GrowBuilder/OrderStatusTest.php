<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\OrderStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_all_static_constructors(): void
    {
        $this->assertEquals('pending', OrderStatus::pending()->value());
        $this->assertEquals('payment_pending', OrderStatus::paymentPending()->value());
        $this->assertEquals('paid', OrderStatus::paid()->value());
        $this->assertEquals('processing', OrderStatus::processing()->value());
        $this->assertEquals('shipped', OrderStatus::shipped()->value());
        $this->assertEquals('delivered', OrderStatus::delivered()->value());
        $this->assertEquals('completed', OrderStatus::completed()->value());
        $this->assertEquals('cancelled', OrderStatus::cancelled()->value());
        $this->assertEquals('refunded', OrderStatus::refunded()->value());
    }

    public function test_from_string(): void
    {
        $this->assertTrue(OrderStatus::fromString('shipped')->isShipped());
    }

    public function test_from_string_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OrderStatus::fromString('nonexistent');
    }

    public function test_labels(): void
    {
        $this->assertEquals('Pending', OrderStatus::pending()->label());
        $this->assertEquals('Awaiting Payment', OrderStatus::paymentPending()->label());
        $this->assertEquals('Shipped', OrderStatus::shipped()->label());
        $this->assertEquals('Completed', OrderStatus::completed()->label());
    }

    public function test_colors(): void
    {
        $this->assertEquals('yellow', OrderStatus::pending()->color());
        $this->assertEquals('green', OrderStatus::completed()->color());
        $this->assertEquals('red', OrderStatus::cancelled()->color());
    }

    public function test_is_methods(): void
    {
        $this->assertTrue(OrderStatus::pending()->isPending());
        $this->assertTrue(OrderStatus::paid()->isPaid());
        $this->assertTrue(OrderStatus::shipped()->isShipped());
        $this->assertTrue(OrderStatus::delivered()->isDelivered());
        $this->assertTrue(OrderStatus::completed()->isCompleted());
        $this->assertTrue(OrderStatus::cancelled()->isCancelled());
        $this->assertTrue(OrderStatus::refunded()->isRefunded());
    }

    public function test_all_returns_all_statuses(): void
    {
        $all = OrderStatus::all();
        $this->assertCount(9, $all);
        $this->assertContains('pending', $all);
        $this->assertContains('refunded', $all);
    }

    // State machine transitions

    public function test_pending_can_transition_to_payment_pending(): void
    {
        $this->assertTrue(OrderStatus::pending()->canTransitionTo(OrderStatus::paymentPending()));
    }

    public function test_pending_can_transition_to_cancelled(): void
    {
        $this->assertTrue(OrderStatus::pending()->canTransitionTo(OrderStatus::cancelled()));
    }

    public function test_pending_cannot_skip_to_shipped(): void
    {
        $this->assertFalse(OrderStatus::pending()->canTransitionTo(OrderStatus::shipped()));
    }

    public function test_payment_pending_can_transition_to_paid(): void
    {
        $this->assertTrue(OrderStatus::paymentPending()->canTransitionTo(OrderStatus::paid()));
    }

    public function test_paid_can_transition_to_processing(): void
    {
        $this->assertTrue(OrderStatus::paid()->canTransitionTo(OrderStatus::processing()));
    }

    public function test_paid_can_transition_to_refunded(): void
    {
        $this->assertTrue(OrderStatus::paid()->canTransitionTo(OrderStatus::refunded()));
    }

    public function test_processing_can_transition_to_shipped(): void
    {
        $this->assertTrue(OrderStatus::processing()->canTransitionTo(OrderStatus::shipped()));
    }

    public function test_shipped_can_transition_to_delivered(): void
    {
        $this->assertTrue(OrderStatus::shipped()->canTransitionTo(OrderStatus::delivered()));
    }

    public function test_delivered_can_transition_to_completed(): void
    {
        $this->assertTrue(OrderStatus::delivered()->canTransitionTo(OrderStatus::completed()));
    }

    public function test_delivered_can_transition_to_refunded(): void
    {
        $this->assertTrue(OrderStatus::delivered()->canTransitionTo(OrderStatus::refunded()));
    }

    public function test_completed_can_transition_to_refunded(): void
    {
        $this->assertTrue(OrderStatus::completed()->canTransitionTo(OrderStatus::refunded()));
    }

    public function test_cancelled_cannot_transition(): void
    {
        $this->assertFalse(OrderStatus::cancelled()->canTransitionTo(OrderStatus::paid()));
        $this->assertFalse(OrderStatus::cancelled()->canTransitionTo(OrderStatus::pending()));
    }

    public function test_refunded_cannot_transition(): void
    {
        $this->assertFalse(OrderStatus::refunded()->canTransitionTo(OrderStatus::pending()));
        $this->assertFalse(OrderStatus::refunded()->canTransitionTo(OrderStatus::paid()));
    }

    public function test_equals(): void
    {
        $this->assertTrue(OrderStatus::paid()->equals(OrderStatus::paid()));
        $this->assertFalse(OrderStatus::paid()->equals(OrderStatus::pending()));
    }

    public function test_full_order_lifecycle_transitions(): void
    {
        $this->assertTrue(OrderStatus::pending()->canTransitionTo(OrderStatus::paymentPending()));
        $this->assertTrue(OrderStatus::paymentPending()->canTransitionTo(OrderStatus::paid()));
        $this->assertTrue(OrderStatus::paid()->canTransitionTo(OrderStatus::processing()));
        $this->assertTrue(OrderStatus::processing()->canTransitionTo(OrderStatus::shipped()));
        $this->assertTrue(OrderStatus::shipped()->canTransitionTo(OrderStatus::delivered()));
        $this->assertTrue(OrderStatus::delivered()->canTransitionTo(OrderStatus::completed()));
    }
}
