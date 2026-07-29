<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\Entities\Seller;
use App\Domain\Marketplace\ValueObjects\TrustLevel;
use App\Domain\Marketplace\ValueObjects\KycStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SellerEntityTest extends TestCase
{
    private function makeSeller(array $overrides = []): Seller
    {
        $params = array_merge([
            'id' => 1,
            'userId' => 10,
            'businessName' => 'Test Shop',
            'businessType' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trustLevel' => TrustLevel::new(),
            'kycStatus' => KycStatus::approved(),
            'kycDocuments' => ['nrc_front' => 'path/to/nrc.jpg'],
            'totalOrders' => 5,
            'completedOrders' => 3,
            'totalSalesAmount' => 150000,
            'disputeRate' => 0.0,
            'cancellationRate' => 0.0,
            'rating' => 4.5,
            'isActive' => true,
            'commissionRate' => null,
            'phone' => '0977123456',
            'email' => 'shop@test.com',
            'description' => 'A test shop',
            'logoPath' => null,
            'coverImagePath' => null,
            'kycRejectionReason' => null,
            'tierCalculatedAt' => null,
        ], $overrides);

        return new Seller(...$params);
    }

    #[Test]
    public function accepts_orders_when_active_and_kyc_approved(): void
    {
        $seller = $this->makeSeller();
        $this->assertTrue($seller->canAcceptOrders());
    }

    #[Test]
    public function rejects_orders_when_inactive(): void
    {
        $seller = $this->makeSeller(['isActive' => false]);
        $this->assertFalse($seller->canAcceptOrders());
    }

    #[Test]
    public function rejects_orders_when_kyc_not_approved(): void
    {
        $seller = $this->makeSeller(['kycStatus' => KycStatus::pending()]);
        $this->assertFalse($seller->canAcceptOrders());
    }

    #[Test]
    public function new_to_verified_upgrade_at_5_orders(): void
    {
        $seller = $this->makeSeller(['totalOrders' => 5]);
        $this->assertTrue($seller->canUpgradeTrustLevel());
        $this->assertEquals('verified', $seller->getNextTrustLevel()->value());
    }

    #[Test]
    public function new_cannot_upgrade_below_5_orders(): void
    {
        $seller = $this->makeSeller(['totalOrders' => 4]);
        $this->assertFalse($seller->canUpgradeTrustLevel());
    }

    #[Test]
    public function new_cannot_upgrade_without_kyc_approval(): void
    {
        $seller = $this->makeSeller([
            'totalOrders' => 5,
            'kycStatus' => KycStatus::pending(),
        ]);
        $this->assertFalse($seller->canUpgradeTrustLevel());
    }

    #[Test]
    public function verified_to_trusted_upgrade_at_50_orders(): void
    {
        $seller = $this->makeSeller([
            'trustLevel' => TrustLevel::verified(),
            'totalOrders' => 50,
            'rating' => 4.5,
        ]);
        $this->assertTrue($seller->canUpgradeTrustLevel());
        $this->assertEquals('trusted', $seller->getNextTrustLevel()->value());
    }

    #[Test]
    public function verified_cannot_upgrade_below_rating(): void
    {
        $seller = $this->makeSeller([
            'trustLevel' => TrustLevel::verified(),
            'totalOrders' => 50,
            'rating' => 4.0,
        ]);
        $this->assertFalse($seller->canUpgradeTrustLevel());
    }

    #[Test]
    public function trusted_to_top_upgrade_at_200_orders(): void
    {
        $seller = $this->makeSeller([
            'trustLevel' => TrustLevel::trusted(),
            'totalOrders' => 200,
            'rating' => 4.8,
        ]);
        $this->assertTrue($seller->canUpgradeTrustLevel());
        $this->assertEquals('top', $seller->getNextTrustLevel()->value());
    }

    #[Test]
    public function top_cannot_upgrade(): void
    {
        $seller = $this->makeSeller([
            'trustLevel' => TrustLevel::top(),
            'totalOrders' => 999,
            'rating' => 5.0,
        ]);
        $this->assertFalse($seller->canUpgradeTrustLevel());
        $this->assertNull($seller->getNextTrustLevel());
    }

    #[Test]
    public function uses_custom_commission_rate_when_set(): void
    {
        $seller = $this->makeSeller(['commissionRate' => 7.5]);
        $this->assertEquals(7.5, $seller->getEffectiveCommissionRate());
    }

    #[Test]
    public function falls_back_to_tier_rate_when_no_custom_rate(): void
    {
        $seller = $this->makeSeller(['trustLevel' => TrustLevel::trusted()]);
        $tierRates = ['new' => 10, 'verified' => 10, 'trusted' => 8, 'top' => 5];
        $this->assertEquals(8, $seller->getEffectiveCommissionRate($tierRates));
    }

    #[Test]
    public function toArray_returns_all_fields(): void
    {
        $seller = $this->makeSeller();
        $data = $seller->toArray();
        $this->assertEquals('Test Shop', $data['business_name']);
        $this->assertEquals('new', $data['trust_level']);
        $this->assertEquals('approved', $data['kyc_status']);
        $this->assertTrue($data['is_active']);
    }
}
