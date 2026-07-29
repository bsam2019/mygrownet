<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\Entities\Order;
use App\Domain\Marketplace\ValueObjects\OrderStatus;
use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use App\Domain\Marketplace\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderEntityTest extends TestCase
{
    private function makeOrder(array $overrides = []): Order
    {
        $params = array_merge([
            'id' => 1,
            'orderNumber' => 'ORD-001',
            'buyerId' => 10,
            'sellerId' => 20,
            'status' => OrderStatus::pending(),
            'subtotal' => Money::fromKwacha(100.00),
            'deliveryFee' => Money::fromKwacha(15.00),
            'total' => Money::fromKwacha(115.00),
            'deliveryMethod' => DeliveryMethod::courier(),
            'deliveryAddress' => ['province' => 'Lusaka', 'district' => 'Lusaka'],
            'deliveryNotes' => null,
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'unit_price' => 5000, 'total_price' => 10000],
            ],
        ], $overrides);

        return new Order(...$params);
    }

    #[Test]
    public function can_be_shipped_when_paid(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::paid()]);
        $this->assertTrue($order->canBeShipped());
    }

    #[Test]
    public function cannot_be_shipped_when_pending(): void
    {
        $this->assertFalse($this->makeOrder()->canBeShipped());
    }

    #[Test]
    public function can_be_delivered_when_shipped(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::shipped()]);
        $this->assertTrue($order->canBeDelivered());
    }

    #[Test]
    public function can_be_confirmed_when_delivered(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::delivered()]);
        $this->assertTrue($order->canBeConfirmed());
    }

    #[Test]
    public function can_be_cancelled_when_pending(): void
    {
        $this->assertTrue($this->makeOrder()->canBeCancelled());
    }

    #[Test]
    public function can_be_cancelled_when_paid(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::paid()]);
        $this->assertTrue($order->canBeCancelled());
    }

    #[Test]
    public function cannot_be_cancelled_when_shipped(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::shipped()]);
        $this->assertFalse($order->canBeCancelled());
    }

    #[Test]
    public function can_be_disputed_when_delivered_and_not_confirmed(): void
    {
        $order = $this->makeOrder([
            'status' => OrderStatus::delivered(),
            'confirmedAt' => null,
        ]);
        $this->assertTrue($order->canBeDisputed());
    }

    #[Test]
    public function cannot_be_disputed_after_confirmed(): void
    {
        $order = $this->makeOrder([
            'status' => OrderStatus::delivered(),
            'confirmedAt' => new \DateTimeImmutable('-2 days'),
        ]);
        $this->assertFalse($order->canBeDisputed());
    }

    #[Test]
    public function should_auto_release_after_7_days(): void
    {
        $order = $this->makeOrder([
            'status' => OrderStatus::delivered(),
            'deliveredAt' => new \DateTimeImmutable('-8 days'),
        ]);
        $this->assertTrue($order->shouldAutoRelease());
    }

    #[Test]
    public function should_not_auto_release_before_7_days(): void
    {
        $order = $this->makeOrder([
            'status' => OrderStatus::delivered(),
            'deliveredAt' => new \DateTimeImmutable('-3 days'),
        ]);
        $this->assertFalse($order->shouldAutoRelease());
    }

    #[Test]
    public function should_not_auto_release_if_not_delivered(): void
    {
        $this->assertFalse($this->makeOrder()->shouldAutoRelease());
    }

    #[Test]
    public function toArray_returns_all_fields(): void
    {
        $order = $this->makeOrder();
        $data = $order->toArray();
        $this->assertEquals('ORD-001', $data['order_number']);
        $this->assertEquals('pending', $data['status']);
        $this->assertEquals(11500, $data['total']);
    }
}
