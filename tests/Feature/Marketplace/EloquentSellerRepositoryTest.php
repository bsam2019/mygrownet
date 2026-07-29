<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;
use App\Models\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentSellerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SellerRepositoryInterface $repo;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(SellerRepositoryInterface::class);
        $this->user = User::factory()->create();
    }

    private function createSellerModel(array $overrides = []): MarketplaceSeller
    {
        return MarketplaceSeller::create(array_merge([
            'user_id' => $this->user->id,
            'business_name' => 'Test Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved',
            'kyc_documents' => ['nrc_front' => 'nrc.jpg'],
            'total_orders' => 0,
            'completed_orders' => 0,
            'total_sales_amount' => 0,
            'dispute_rate' => 0,
            'cancellation_rate' => 0,
            'rating' => 0,
            'is_active' => true,
        ], $overrides));
    }

    #[Test]
    public function finds_by_id(): void
    {
        $model = $this->createSellerModel();
        $seller = $this->repo->findById($model->id);
        $this->assertNotNull($seller);
        $this->assertEquals('Test Shop', $seller->businessName);
    }

    #[Test]
    public function finds_by_user_id(): void
    {
        $this->createSellerModel();
        $seller = $this->repo->findByUserId($this->user->id);
        $this->assertNotNull($seller);
        $this->assertTrue($seller->kycStatus->isApproved());
    }

    #[Test]
    public function save_creates_new_seller(): void
    {
        $seller = new \App\Domain\Marketplace\Entities\Seller(
            id: null,
            userId: $this->user->id,
            businessName: 'New Shop',
            businessType: 'retail',
            province: 'Lusaka',
            district: 'Lusaka',
            trustLevel: TrustLevel::new(),
            kycStatus: KycStatus::pending(),
            kycDocuments: [],
            totalOrders: 0,
            completedOrders: 0,
            totalSalesAmount: 0,
            disputeRate: 0.0,
            cancellationRate: 0.0,
            rating: 0.0,
            isActive: true,
            commissionRate: null,
            phone: '0977000000',
            email: null,
            description: 'A new shop',
            logoPath: null,
            coverImagePath: null,
            kycRejectionReason: null,
            tierCalculatedAt: null,
        );

        $result = $this->repo->save($seller);
        $this->assertNotNull($result->id);
        $this->assertEquals('New Shop', $result->businessName);
        $this->assertTrue($result->kycStatus->isPending());
    }

    #[Test]
    public function save_updates_existing_seller(): void
    {
        $model = $this->createSellerModel();
        $seller = $this->repo->findById($model->id);
        $updated = new \App\Domain\Marketplace\Entities\Seller(
            id: $seller->id,
            userId: $seller->userId,
            businessName: 'Updated Shop',
            businessType: $seller->businessType,
            province: $seller->province,
            district: $seller->district,
            trustLevel: TrustLevel::verified(),
            kycStatus: $seller->kycStatus,
            kycDocuments: $seller->kycDocuments,
            totalOrders: 10,
            completedOrders: $seller->completedOrders,
            totalSalesAmount: $seller->totalSalesAmount,
            disputeRate: $seller->disputeRate,
            cancellationRate: $seller->cancellationRate,
            rating: 4.5,
            isActive: $seller->isActive,
            commissionRate: $seller->commissionRate,
            phone: $seller->phone,
            email: $seller->email,
            description: $seller->description,
            logoPath: $seller->logoPath,
            coverImagePath: $seller->coverImagePath,
            kycRejectionReason: $seller->kycRejectionReason,
            tierCalculatedAt: $seller->tierCalculatedAt,
        );

        $result = $this->repo->save($updated);
        $this->assertEquals('Updated Shop', $result->businessName);
        $this->assertTrue($result->trustLevel->isVerified());
        $this->assertEquals(10, $result->totalOrders);
    }

    #[Test]
    public function update_trust_level(): void
    {
        $model = $this->createSellerModel();
        $this->repo->updateTrustLevel($model->id, 'trusted');
        $this->assertDatabaseHas('marketplace_sellers', ['id' => $model->id, 'trust_level' => 'trusted']);
    }

    #[Test]
    public function update_kyc_status(): void
    {
        $model = $this->createSellerModel(['kyc_status' => 'pending']);
        $this->repo->updateKycStatus($model->id, 'approved');
        $this->assertDatabaseHas('marketplace_sellers', ['id' => $model->id, 'kyc_status' => 'approved']);
    }

    #[Test]
    public function increment_order_count(): void
    {
        $model = $this->createSellerModel(['total_orders' => 5]);
        $this->repo->incrementOrderCount($model->id);
        $this->assertDatabaseHas('marketplace_sellers', ['id' => $model->id, 'total_orders' => 6]);
    }

    #[Test]
    public function balance_operations(): void
    {
        $model = $this->createSellerModel();
        $this->repo->incrementBalance($model->id, 50000);
        $this->assertEquals(50000, $this->repo->getBalance($model->id));

        $this->repo->decrementBalance($model->id, 10000);
        $this->assertEquals(40000, $this->repo->getBalance($model->id));
    }
}
