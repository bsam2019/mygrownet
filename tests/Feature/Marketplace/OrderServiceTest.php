<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\OrderService;
use App\Domain\Marketplace\Services\EscrowService;
use App\Domain\Marketplace\Services\SellerService;
use App\Domain\GrowNet\Services\LgrActivityTrackingService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentEscrowRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceCategory;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceProduct;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private int $buyerId;
    private int $sellerId;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();
        $buyer = User::factory()->create();
        $this->buyerId = $buyer->id;

        $sellerUser = User::factory()->create();
        $seller = MarketplaceSeller::create([
            'user_id' => $sellerUser->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved', 'kyc_documents' => [],
            'total_orders' => 0, 'completed_orders' => 0, 'total_sales_amount' => 0,
            'dispute_rate' => 0, 'cancellation_rate' => 0, 'rating' => 0,
            'is_active' => true, 'commission_rate' => 10.0,
            'phone' => '0977000000', 'email' => 'test@example.com',
        ]);
        $this->sellerId = $seller->id;

        $category = MarketplaceCategory::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = MarketplaceProduct::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Desc',
            'price' => 15000,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);
        $this->productId = $product->id;

        $lgrTracking = $this->createMock(LgrActivityTrackingService::class);
        $productRepo = new EloquentProductRepository();
        $sellerRepo = new EloquentSellerRepository();
        $escrowRepo = new EloquentEscrowRepository();

        $this->service = new OrderService(
            new EloquentOrderRepository(),
            $productRepo,
            new EscrowService($escrowRepo, $sellerRepo),
            new SellerService($sellerRepo),
            $lgrTracking,
        );
    }

    public function test_create_order(): void
    {
        $result = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 2],
        ], [
            'method' => 'courier',
            'name' => 'John Doe',
            'phone' => '0977000000',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
        ]);

        $this->assertNotNull($result['id']);
        $this->assertEquals('pending', $result['status']);
        $this->assertEquals(35000, $result['total']); // 15000*2 + 5000 delivery
        $this->assertStringStartsWith('MKT-', $result['order_number']);
    }

    public function test_create_order_throws_for_insufficient_stock(): void
    {
        $this->expectException(\Exception::class);
        $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 99],
        ], [
            'method' => 'courier', 'name' => 'John', 'phone' => '0977000000',
        ]);
    }

    public function test_markAsPaid(): void
    {
        $order = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], [
            'method' => 'pickup', 'name' => 'John', 'phone' => '0977000000',
        ]);

        $result = $this->service->markAsPaid($order['id'], 'PAY-REF-001');
        $this->assertEquals('paid', $result['status']);
    }

    public function test_markAsShipped(): void
    {
        $order = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'courier', 'name' => 'John', 'phone' => '0977000000']);
        $this->service->markAsPaid($order['id'], 'PAY-REF');

        $result = $this->service->markAsShipped($order['id']);
        $this->assertEquals('shipped', $result['status']);
    }

    public function test_markAsDelivered(): void
    {
        $order = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'courier', 'name' => 'John', 'phone' => '0977000000']);
        $this->service->markAsPaid($order['id'], 'PAY-REF');
        $this->service->markAsShipped($order['id']);

        $result = $this->service->markAsDelivered($order['id']);
        $this->assertEquals('delivered', $result['status']);
    }

    public function test_confirmReceipt_completes_order(): void
    {
        $order = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'pickup', 'name' => 'John', 'phone' => '0977000000']);
        $this->service->markAsPaid($order['id'], 'PAY-REF');

        MarketplaceOrder::where('id', $order['id'])->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $result = $this->service->confirmReceipt($order['id']);
        $this->assertEquals('completed', $result['status']);
    }

    public function test_cancelOrder_pending(): void
    {
        $order = $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'pickup', 'name' => 'John', 'phone' => '0977000000']);

        $result = $this->service->cancelOrder($order['id'], 'Changed mind', 'buyer');
        $this->assertEquals('cancelled', $result['status']);
    }

    public function test_getByBuyer(): void
    {
        $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'pickup', 'name' => 'John', 'phone' => '0977000000']);

        $orders = $this->service->getByBuyer($this->buyerId);
        $this->assertCount(1, $orders);
    }

    public function test_getBySeller(): void
    {
        $this->service->createOrder($this->buyerId, [
            ['product_id' => $this->productId, 'seller_id' => $this->sellerId, 'quantity' => 1],
        ], ['method' => 'pickup', 'name' => 'John', 'phone' => '0977000000']);

        $orders = $this->service->getBySeller($this->sellerId);
        $this->assertCount(1, $orders);
    }
}
