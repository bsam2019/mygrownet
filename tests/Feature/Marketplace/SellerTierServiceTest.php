<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\SellerTierService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SellerTierServiceTest extends TestCase
{
    use RefreshDatabase;

    private SellerTierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SellerTierService(new EloquentSellerRepository());
    }

    public function test_getCommissionRate_uses_config_when_none_set(): void
    {
        $seller = ['trust_level' => 'new', 'commission_rate' => null];
        $rate = $this->service->getCommissionRate($seller);
        $this->assertEquals(10.0, $rate);
    }

    public function test_getCommissionRate_uses_custom_rate(): void
    {
        $seller = ['trust_level' => 'top', 'commission_rate' => 5.0];
        $rate = $this->service->getCommissionRate($seller);
        $this->assertEquals(5.0, $rate);
    }

    public function test_calculateCommission(): void
    {
        $seller = ['trust_level' => 'trusted', 'commission_rate' => 8.0];
        $result = $this->service->calculateCommission($seller, 100000);

        $this->assertArrayHasKey('commission_amount', $result);
        $this->assertArrayHasKey('seller_payout', $result);
        $this->assertEquals(100000, $result['order_amount']);
    }

    public function test_getTierInfo_returns_all_tiers(): void
    {
        $tiers = SellerTierService::getTierInfo();
        $this->assertCount(4, $tiers);
        $this->assertArrayHasKey('new', $tiers);
        $this->assertArrayHasKey('top', $tiers);
    }

    public function test_getTierProgress_for_new_seller(): void
    {
        $seller = [
            'trust_level' => 'new',
            'completed_orders' => 0,
            'total_sales_amount' => 0,
            'rating' => 0,
            'dispute_rate' => 0,
            'cancellation_rate' => 0,
            'created_at' => now()->toDateTimeString(),
        ];

        $progress = $this->service->getTierProgress($seller);
        $this->assertEquals('new', $progress['current_tier']);
        $this->assertEquals('verified', $progress['next_tier']);
        $this->assertFalse($progress['is_max_tier']);
    }

    public function test_getTierProgress_for_top_seller(): void
    {
        $seller = [
            'trust_level' => 'top',
            'completed_orders' => 500,
            'total_sales_amount' => 10000000,
            'rating' => 5.0,
            'dispute_rate' => 0,
            'cancellation_rate' => 0,
            'created_at' => now()->subYear()->toDateTimeString(),
        ];

        $progress = $this->service->getTierProgress($seller);
        $this->assertTrue($progress['is_max_tier']);
        $this->assertNull($progress['next_tier']);
    }

    public function test_recalculateTier_moves_verified_to_trusted(): void
    {
        $user = User::factory()->create();
        $seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka', 'district' => 'Lusaka',
            'trust_level' => 'verified',
            'kyc_status' => 'approved', 'kyc_documents' => [],
            'total_orders' => 0, 'completed_orders' => 0, 'total_sales_amount' => 0,
            'dispute_rate' => 0, 'cancellation_rate' => 0, 'rating' => 0,
            'is_active' => true, 'commission_rate' => 10.0,
            'phone' => '0977000000', 'email' => 'test@example.com',
        ]);

        // Set created_at 60 days ago (trusted tier requires 30 days)
        DB::table('marketplace_sellers')
            ->where('id', $seller->id)
            ->update(['created_at' => now()->subDays(60)]);

        // Create 20+ completed orders with sufficient sales volume
        for ($i = 0; $i < 25; $i++) {
            MarketplaceOrder::create([
                'order_number' => 'MKT-TIER-' . $i,
                'buyer_id' => $user->id,
                'seller_id' => $seller->id,
                'status' => 'completed',
                'subtotal' => 25000, 'delivery_fee' => 0, 'total' => 25000,
                'delivery_method' => 'pickup',
                'delivery_address' => '{}',
            ]);
        }

        // Update rating directly to meet 4.0 threshold
        DB::table('marketplace_sellers')
            ->where('id', $seller->id)
            ->update(['rating' => 4.5]);

        $this->service->recalculateTier($seller->id);

        $seller->refresh();
        $this->assertEquals('trusted', $seller->trust_level);
    }

    public function test_updateSellerMetrics(): void
    {
        $user = User::factory()->create();
        $seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka', 'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved', 'kyc_documents' => [],
            'total_orders' => 0, 'completed_orders' => 0, 'total_sales_amount' => 0,
            'dispute_rate' => 0, 'cancellation_rate' => 0, 'rating' => 0,
            'is_active' => true, 'commission_rate' => 10.0,
            'phone' => '0977000000', 'email' => 'test@example.com',
        ]);

        MarketplaceOrder::create([
            'order_number' => 'MKT-METRIC-1',
            'buyer_id' => $user->id,
            'seller_id' => $seller->id,
            'status' => 'completed',
            'subtotal' => 30000, 'delivery_fee' => 0, 'total' => 30000,
            'delivery_method' => 'pickup',
            'delivery_address' => '{}',
        ]);

        $this->service->updateSellerMetrics($seller->id);

        $seller->refresh();
        $this->assertEquals(1, $seller->completed_orders);
        $this->assertEquals(30000, $seller->total_sales_amount);
    }
}
