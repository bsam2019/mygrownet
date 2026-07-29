<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Repositories\EscrowRepositoryInterface;
use App\Models\Marketplace\MarketplaceEscrow;
use App\Models\Marketplace\MarketplaceOrder;
use App\Models\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentEscrowRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EscrowRepositoryInterface $repo;
    private MarketplaceSeller $seller;
    private MarketplaceOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(EscrowRepositoryInterface::class);
        $buyer = User::factory()->create();
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
        $this->order = MarketplaceOrder::create([
            'order_number' => 'MP-ESC-001',
            'buyer_id' => $buyer->id,
            'seller_id' => $this->seller->id,
            'status' => 'paid',
            'subtotal' => 50000,
            'delivery_fee' => 0,
            'total' => 50000,
            'delivery_method' => 'self',
            'delivery_address' => [],
        ]);
    }

    #[Test]
    public function creates_and_finds_by_order_id(): void
    {
        $escrow = $this->repo->create([
            'order_id' => $this->order->id,
            'amount' => 50000,
            'status' => 'held',
            'held_at' => now(),
        ]);

        $this->assertNotNull($escrow['id']);
        $this->assertEquals(50000, $escrow['amount']);

        $found = $this->repo->findByOrderId($this->order->id);
        $this->assertNotNull($found);
        $this->assertEquals('held', $found['status']);
    }

    #[Test]
    public function updates_by_order_id(): void
    {
        $this->repo->create([
            'order_id' => $this->order->id,
            'amount' => 50000,
            'status' => 'held',
            'held_at' => now(),
        ]);

        $this->repo->updateByOrderId($this->order->id, [
            'status' => 'released',
            'released_at' => now(),
        ]);

        $found = $this->repo->findByOrderId($this->order->id);
        $this->assertEquals('released', $found['status']);
    }

    #[Test]
    public function get_total_held_balance(): void
    {
        $this->repo->create(['order_id' => $this->order->id, 'amount' => 30000, 'status' => 'held', 'held_at' => now()]);
        $this->repo->create(['order_id' => $this->order->id + 1, 'amount' => 20000, 'status' => 'held', 'held_at' => now()]);
        $this->repo->create(['order_id' => $this->order->id + 2, 'amount' => 50000, 'status' => 'released', 'released_at' => now()]);

        $this->assertEquals(50000, $this->repo->getTotalHeldBalance());
    }

    #[Test]
    public function get_seller_pending_balance(): void
    {
        $this->repo->create(['order_id' => $this->order->id, 'amount' => 30000, 'status' => 'held', 'held_at' => now()]);
        $this->assertEquals(30000, $this->repo->getSellerPendingBalance($this->seller->id));
    }
}
