<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\ValueObjects\OrderStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    #[Test]
    public function creates_via_named_constructors(): void
    {
        $this->assertTrue(OrderStatus::pending()->isPending());
        $this->assertTrue(OrderStatus::paid()->isPaid());
        $this->assertTrue(OrderStatus::processing()->isProcessing());
        $this->assertTrue(OrderStatus::shipped()->isShipped());
        $this->assertTrue(OrderStatus::delivered()->isDelivered());
        $this->assertTrue(OrderStatus::completed()->isCompleted());
        $this->assertTrue(OrderStatus::cancelled()->isCancelled());
        $this->assertTrue(OrderStatus::disputed()->isDisputed());
        $this->assertTrue(OrderStatus::refunded()->isRefunded());
    }

    #[Test]
    public function creates_from_string(): void
    {
        $this->assertTrue(OrderStatus::fromString('shipped')->isShipped());
    }

    #[Test]
    public function value_returns_raw_string(): void
    {
        $this->assertEquals('paid', OrderStatus::paid()->value());
    }

    #[Test]
    public function labels_are_correct(): void
    {
        $this->assertEquals('Pending Payment', OrderStatus::pending()->label());
        $this->assertEquals('Paid', OrderStatus::paid()->label());
        $this->assertEquals('Processing', OrderStatus::processing()->label());
        $this->assertEquals('Shipped', OrderStatus::shipped()->label());
        $this->assertEquals('Delivered', OrderStatus::delivered()->label());
        $this->assertEquals('Completed', OrderStatus::completed()->label());
        $this->assertEquals('Cancelled', OrderStatus::cancelled()->label());
        $this->assertEquals('Disputed', OrderStatus::disputed()->label());
        $this->assertEquals('Refunded', OrderStatus::refunded()->label());
    }

    #[Test]
    public function colors_are_correct(): void
    {
        $this->assertEquals('yellow', OrderStatus::pending()->color());
        $this->assertEquals('blue', OrderStatus::paid()->color());
        $this->assertEquals('indigo', OrderStatus::processing()->color());
        $this->assertEquals('purple', OrderStatus::shipped()->color());
        $this->assertEquals('teal', OrderStatus::delivered()->color());
        $this->assertEquals('green', OrderStatus::completed()->color());
        $this->assertEquals('gray', OrderStatus::cancelled()->color());
        $this->assertEquals('red', OrderStatus::disputed()->color());
        $this->assertEquals('orange', OrderStatus::refunded()->color());
    }

    #[Test]
    public function rejects_invalid_status(): void
    {
        $this->expectException(InvalidArgumentException::class);
        OrderStatus::fromString('unknown');
    }
}
