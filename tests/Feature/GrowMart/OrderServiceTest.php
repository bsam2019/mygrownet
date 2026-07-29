<?php

namespace Tests\Feature\GrowMart;

use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Services\OrderService;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowMartProduct $product;
    private CartService $cartService;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAdminRole();
        $this->user = User::factory()->create();
        $category = GrowMartCategory::factory()->create();
        $warehouse = GrowMartWarehouse::factory()->create();
        $this->product = GrowMartProduct::factory()->create([
            'category_id' => $category->id,
            'price' => 1000,
            'status' => 'active',
        ]);
        GrowMartInventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);
        $this->cartService = app(CartService::class);
        $this->orderService = app(OrderService::class);

        Notification::fake();
    }

    private function seedAdminRole(): void
    {
        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'admin',
                'guard_name' => 'web',
                'slug' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function addToCart(int $quantity = 3): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, $quantity);
    }

    #[Test]
    public function creates_order_from_cart(): void
    {
        $this->addToCart();
        $order = $this->orderService->createOrder($this->user->id, [
            'delivery_method' => 'pickup',
            'contact_phone' => '0977123456',
        ]);
        $this->assertStringStartsWith('GM-', $order['order_number']);
        $this->assertEquals('pending', $order['status']);
        $this->assertEquals(3000, $order['total']);
    }

    #[Test]
    public function creates_order_with_delivery_fee(): void
    {
        $this->addToCart();
        $order = $this->orderService->createOrder($this->user->id, [
            'delivery_method' => 'yango',
            'delivery_zone' => 'Lusaka',
            'delivery_address' => '123 Main St',
            'contact_phone' => '0977123456',
        ]);
        $this->assertEquals(3000, $order['subtotal']);
        $this->assertEquals(3000, $order['delivery_fee']);
        $this->assertEquals(6000, $order['total']);
    }

    #[Test]
    public function rejects_empty_cart(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cart is empty');
        $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
    }

    #[Test]
    public function rejects_insufficient_stock(): void
    {
        $this->addToCart(999);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');
        $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
    }

    #[Test]
    public function deducts_stock_after_order(): void
    {
        $this->addToCart(10);
        $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $remaining = (int) $this->product->inventory()->sum('quantity');
        $this->assertEquals(40, $remaining);
    }

    #[Test]
    public function lists_orders_for_user(): void
    {
        $this->addToCart(1);
        $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $orders = $this->orderService->getOrdersForUser($this->user->id);
        $this->assertEquals(1, $orders['total']);
    }

    #[Test]
    public function cancels_pending_order(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $cancelled = $this->orderService->cancelOrder($order['id'], $this->user->id);
        $this->assertEquals('cancelled', $cancelled['status']);
        $this->assertNotNull($cancelled['cancelled_at']);
    }

    #[Test]
    public function prevents_cancelling_delivered_order(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $this->orderService->updateStatus($order['id'], 'delivered');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cannot be cancelled');
        $this->orderService->cancelOrder($order['id'], $this->user->id);
    }

    #[Test]
    public function updates_order_status(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $updated = $this->orderService->updateStatus($order['id'], 'delivered');
        $this->assertEquals('delivered', $updated['status']);
        $this->assertNotNull($updated['delivered_at']);
    }

    #[Test]
    public function updates_payment_status(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $updated = $this->orderService->updatePayment($order['id'], 'paid');
        $this->assertEquals('paid', $updated['payment_status']);
        $this->assertNotNull($updated['paid_at']);
    }

    #[Test]
    public function updates_tracking(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $updated = $this->orderService->updateTracking($order['id'], [
            'tracking_number' => 'TRACK123',
            'tracking_url' => 'https://track.example.com/TRACK123',
        ]);
        $this->assertEquals('TRACK123', $updated['tracking_number']);
        $this->assertCount(1, $updated['tracking_updates']);
    }

    #[Test]
    public function gets_order_by_id(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $found = $this->orderService->getOrder($order['id'], $this->user->id);
        $this->assertEquals($order['id'], $found['id']);
    }

    #[Test]
    public function throws_for_other_users_order(): void
    {
        $this->addToCart(1);
        $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
        $otherUser = User::factory()->create();
        $this->expectException(\RuntimeException::class);
        $this->orderService->getOrder($order['id'], $otherUser->id);
    }
}
