<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\EscrowService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentEscrowRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceEscrow;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscrowServiceTest extends TestCase
{
    use RefreshDatabase;

    private EscrowService $service;
    private int $sellerId;
    private int $orderId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EscrowService(
            new EloquentEscrowRepository(),
            new EloquentSellerRepository(),
        );

        $user = User::factory()->create();
        $seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved',
            'kyc_documents' => [],
            'total_orders' => 0, 'completed_orders' => 0, 'total_sales_amount' => 0,
            'dispute_rate' => 0, 'cancellation_rate' => 0, 'rating' => 0,
            'is_active' => true, 'commission_rate' => 10.0,
            'phone' => '0977000000', 'email' => 'test@example.com',
        ]);
        $this->sellerId = $seller->id;

        $order = MarketplaceOrder::create([
            'order_number' => 'MKT-TEST123',
            'buyer_id' => $user->id,
            'seller_id' => $seller->id,
            'status' => 'paid',
            'subtotal' => 50000, 'delivery_fee' => 5000, 'total' => 55000,
            'delivery_method' => 'courier',
            'delivery_address' => json_encode(['name' => 'Test', 'province' => 'Lusaka']),
            'payment_reference' => 'PAY-REF-001',
        ]);
        $this->orderId = $order->id;
    }

    public function test_holdFunds_creates_escrow(): void
    {
        $result = $this->service->holdFunds($this->orderId, 55000);

        $this->assertNotNull($result['id']);
        $this->assertEquals($this->orderId, $result['order_id']);
        $this->assertEquals(55000, $result['amount']);
        $this->assertEquals('held', $result['status']);
    }

    public function test_releaseFunds_credits_seller(): void
    {
        $this->service->holdFunds($this->orderId, 55000);
        $this->service->releaseFunds($this->orderId, 'buyer_confirmed');

        $escrow = MarketplaceEscrow::where('order_id', $this->orderId)->first();
        $this->assertEquals('released', $escrow->status);

        $balance = (new EloquentSellerRepository())->getBalance($this->sellerId);
        $this->assertEquals(55000, $balance);
    }

    public function test_refundFunds_updates_status(): void
    {
        $this->service->holdFunds($this->orderId, 55000);
        $this->service->refundFunds($this->orderId, 'order_cancelled');

        $escrow = MarketplaceEscrow::where('order_id', $this->orderId)->first();
        $this->assertEquals('refunded', $escrow->status);
    }

    public function test_markAsDisputed(): void
    {
        $this->service->holdFunds($this->orderId, 55000);
        $this->service->markAsDisputed($this->orderId);

        $escrow = MarketplaceEscrow::where('order_id', $this->orderId)->first();
        $this->assertEquals('disputed', $escrow->status);
    }

    public function test_getEscrowBalance(): void
    {
        $this->service->holdFunds($this->orderId, 55000);
        $this->assertEquals(55000, $this->service->getEscrowBalance());
    }

    public function test_getSellerPendingBalance(): void
    {
        $this->service->holdFunds($this->orderId, 55000);
        $this->assertEquals(55000, $this->service->getSellerPendingBalance($this->sellerId));
    }
}
