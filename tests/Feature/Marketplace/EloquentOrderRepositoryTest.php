<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Entities\Order;
use App\Domain\Marketplace\Repositories\OrderRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Domain\Marketplace\ValueObjects\OrderStatus;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceOrderItem;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepositoryInterface $repo;
    private MarketplaceSeller $seller;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(OrderRepositoryInterface::class);
        $this->buyer = User::factory()->create();
        $this->seller = MarketplaceSeller::create([
            'user_id' => User::factory()->create()->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function finds_by_id_with_items(): void
    {
        $order = MarketplaceOrder::create([
            'order_number' => 'MP-001',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'subtotal' => 10000,
            'delivery_fee' => 1500,
            'total' => 11500,
            'delivery_method' => 'courier',
            'delivery_address' => ['province' => 'Lusaka'],
        ]);
        MarketplaceOrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 5000,
            'total_price' => 10000,
        ]);

        $found = $this->repo->findById($order->id);
        $this->assertNotNull($found);
        $this->assertEquals('MP-001', $found->orderNumber);
        $this->assertCount(1, $found->items);
    }

    #[Test]
    public function finds_by_order_number(): void
    {
        MarketplaceOrder::create([
            'order_number' => 'MP-002',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'paid',
            'subtotal' => 5000,
            'delivery_fee' => 0,
            'total' => 5000,
            'delivery_method' => 'pickup',
            'delivery_address' => [],
        ]);

        $found = $this->repo->findByOrderNumber('MP-002');
        $this->assertNotNull($found);
        $this->assertTrue($found->status->isPaid());
    }

    #[Test]
    public function saves_new_order_with_items(): void
    {
        $order = new Order(
            id: null,
            orderNumber: 'MP-003',
            buyerId: $this->buyer->id,
            sellerId: $this->seller->id,
            status: OrderStatus::pending(),
            subtotal: Money::fromNgwee(8000),
            deliveryFee: Money::fromNgwee(2000),
            total: Money::fromNgwee(10000),
            deliveryMethod: DeliveryMethod::courier(),
            deliveryAddress: ['province' => 'Lusaka'],
            deliveryNotes: null,
            items: [
                ['product_id' => 1, 'quantity' => 1, 'unit_price' => 8000, 'total_price' => 8000],
            ],
        );

        $result = $this->repo->save($order);
        $this->assertNotNull($result->id);
        $this->assertEquals('MP-003', $result->orderNumber);
        $this->assertCount(1, $result->items);
    }

    #[Test]
    public function updates_status(): void
    {
        $order = MarketplaceOrder::create([
            'order_number' => 'MP-004',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
        ]);

        $this->repo->updateStatus($order->id, 'paid');
        $this->assertDatabaseHas('marketplace_orders', ['id' => $order->id, 'status' => 'paid']);
    }

    #[Test]
    public function marks_as_delivered(): void
    {
        $order = MarketplaceOrder::create([
            'order_number' => 'MP-005',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'shipped',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
        ]);

        $this->repo->markAsDelivered($order->id);
        $this->assertDatabaseHas('marketplace_orders', ['id' => $order->id, 'status' => 'delivered']);
        $this->assertNotNull(MarketplaceOrder::find($order->id)->delivered_at);
    }

    #[Test]
    public function marks_as_confirmed(): void
    {
        $order = MarketplaceOrder::create([
            'order_number' => 'MP-006',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'delivered',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
        ]);

        $this->repo->markAsConfirmed($order->id);
        $this->assertDatabaseHas('marketplace_orders', ['id' => $order->id, 'status' => 'completed']);
    }

    #[Test]
    public function finds_pending_auto_release(): void
    {
        MarketplaceOrder::create([
            'order_number' => 'MP-007',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'delivered',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
            'delivered_at' => now()->subDays(10),
        ]);
        MarketplaceOrder::create([
            'order_number' => 'MP-008',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'delivered',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
            'delivered_at' => now()->subDays(3),
        ]);

        $pending = $this->repo->findPendingAutoRelease();
        $this->assertCount(1, $pending);
        $this->assertEquals('MP-007', $pending[0]->orderNumber);
    }

    #[Test]
    public function order_number_exists_returns_correctly(): void
    {
        MarketplaceOrder::create([
            'order_number' => 'MP-009',
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
            'delivery_method' => 'self',
            'delivery_address' => [],
        ]);

        $this->assertTrue($this->repo->orderNumberExists('MP-009'));
        $this->assertFalse($this->repo->orderNumberExists('MP-999'));
    }
}
