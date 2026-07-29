<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\PayoutService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentPayoutRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplacePayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private PayoutService $service;
    private int $sellerId;
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->adminId = User::factory()->create()->id;
        $seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new', 'kyc_status' => 'approved', 'kyc_documents' => [],
            'total_orders' => 0, 'completed_orders' => 0, 'total_sales_amount' => 0,
            'dispute_rate' => 0, 'cancellation_rate' => 0, 'rating' => 0,
            'is_active' => true, 'commission_rate' => 10.0,
            'phone' => '0977000000', 'email' => 'test@example.com',
        ]);
        $this->sellerId = $seller->id;

        $this->service = new PayoutService(
            new EloquentSellerRepository(),
            new EloquentPayoutRepository(),
        );
    }

    public function test_getMinimumPayoutAmount(): void
    {
        $this->assertEquals(5000, $this->service->getMinimumPayoutAmount());
    }

    public function test_getAvailableBalance_returns_zero_initially(): void
    {
        $this->assertEquals(0, $this->service->getAvailableBalance($this->sellerId));
    }

    public function test_canRequestPayout_returns_false_when_no_balance(): void
    {
        $result = $this->service->canRequestPayout($this->sellerId);
        $this->assertFalse($result['can_request']);
        $this->assertStringContainsString('Minimum payout', $result['reason']);
    }

    public function test_canRequestPayout_returns_false_when_pending(): void
    {
        $this->seedSellerBalance();
        MarketplacePayout::create([
            'seller_id' => $this->sellerId,
            'amount' => 1000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
            'status' => 'pending',
        ]);

        $result = $this->service->canRequestPayout($this->sellerId);
        $this->assertFalse($result['can_request']);
        $this->assertStringContainsString('pending payout', $result['reason']);
    }

    public function test_canRequestPayout_returns_true_when_eligible(): void
    {
        $this->seedSellerBalance();

        $result = $this->service->canRequestPayout($this->sellerId);
        $this->assertTrue($result['can_request']);
    }

    public function test_createPayoutRequest(): void
    {
        $this->seedSellerBalance();

        $payout = $this->service->createPayoutRequest($this->sellerId, [
            'amount' => 30000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test User',
        ]);

        $this->assertNotNull($payout['id']);
        $this->assertEquals(30000, $payout['amount']);
        $this->assertEquals(30000, $payout['net_amount']);
        $this->assertEquals(0, $payout['commission_deducted']);
        $this->assertEquals('pending', $payout['status']);
        $this->assertStringStartsWith('PO-', $payout['reference']);
    }

    public function test_createPayoutRequest_throws_when_exceeds_balance(): void
    {
        $this->seedSellerBalance();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exceeds available balance');

        $this->service->createPayoutRequest($this->sellerId, [
            'amount' => 100000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
        ]);
    }

    public function test_approvePayout(): void
    {
        $payout = $this->createPendingPayout();

        $result = $this->service->approvePayout($payout['id'], $this->adminId);

        $this->assertEquals('approved', $result['status']);
        $this->assertEquals($this->adminId, $result['approved_by']);
    }

    public function test_rejectPayout_restores_balance(): void
    {
        $this->seedSellerBalance();
        $payout = $this->service->createPayoutRequest($this->sellerId, [
            'amount' => 30000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
        ]);

        $result = $this->service->rejectPayout($payout['id'], $this->adminId, 'Invalid details');

        $this->assertEquals('rejected', $result['status']);

        // Balance should be restored
        $this->assertEquals(50000, $this->service->getAvailableBalance($this->sellerId));
    }

    public function test_markAsProcessing(): void
    {
        $payout = $this->createPendingPayout();
        $this->service->approvePayout($payout['id'], $this->adminId);

        $result = $this->service->markAsProcessing($payout['id'], $this->adminId);

        $this->assertEquals('processing', $result['status']);
    }

    public function test_completePayout(): void
    {
        $payout = $this->createPendingPayout();
        $this->service->approvePayout($payout['id'], $this->adminId);
        $this->service->markAsProcessing($payout['id'], $this->adminId);

        $result = $this->service->completePayout($payout['id'], 'TXN-REF-001');

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals('TXN-REF-001', $result['transaction_reference']);
    }

    public function test_markAsFailed_restores_balance(): void
    {
        $this->seedSellerBalance();
        $payout = $this->service->createPayoutRequest($this->sellerId, [
            'amount' => 30000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
        ]);
        $this->service->approvePayout($payout['id'], $this->adminId);
        $this->service->markAsProcessing($payout['id'], $this->adminId);

        $result = $this->service->markAsFailed($payout['id'], 'API timeout');

        $this->assertEquals('failed', $result['status']);

        // Balance should be restored
        $this->assertEquals(50000, $this->service->getAvailableBalance($this->sellerId));
    }

    public function test_getSellerPayouts(): void
    {
        MarketplacePayout::create([
            'seller_id' => $this->sellerId,
            'amount' => 10000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
            'status' => 'completed',
        ]);

        $payouts = $this->service->getSellerPayouts($this->sellerId);
        $this->assertCount(1, $payouts['data']);
    }

    private function seedSellerBalance(): void
    {
        $repo = new EloquentSellerRepository();
        $repo->incrementBalance($this->sellerId, 50000);
    }

    private function createPendingPayout(): array
    {
        $this->seedSellerBalance();
        return $this->service->createPayoutRequest($this->sellerId, [
            'amount' => 30000,
            'payout_method' => 'momo',
            'account_number' => '0977000000',
            'account_name' => 'Test',
        ]);
    }
}
