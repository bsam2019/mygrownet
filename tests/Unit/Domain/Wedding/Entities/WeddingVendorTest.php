<?php

namespace Tests\Unit\Domain\Wedding\Entities;

use App\Domain\Wedding\Entities\WeddingVendor;
use App\Domain\Wedding\ValueObjects\VendorCategory;
use App\Domain\Wedding\ValueObjects\VendorRating;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingVendorTest extends TestCase
{
    public function test_create(): void
    {
        $vendor = WeddingVendor::create(
            userId: 1,
            businessName: 'Elegant Events',
            category: VendorCategory::planning(),
            contactPerson: 'John Doe',
            phone: '+260970000000',
            email: 'info@elegantevents.com',
            location: 'Lusaka',
            description: 'Professional wedding planning services',
            priceRange: 'K5,000 - K20,000'
        );

        $this->assertNull($vendor->getId());
        $this->assertEquals(1, $vendor->getUserId());
        $this->assertEquals('Elegant Events', $vendor->getBusinessName());
        $this->assertEquals('elegant-events', $vendor->getSlug());
        $this->assertTrue($vendor->getCategory()->equals(VendorCategory::planning()));
        $this->assertEquals('John Doe', $vendor->getContactPerson());
        $this->assertEquals('+260970000000', $vendor->getPhone());
        $this->assertEquals('info@elegantevents.com', $vendor->getEmail());
        $this->assertEquals('Lusaka', $vendor->getLocation());
        $this->assertEquals('Professional wedding planning services', $vendor->getDescription());
        $this->assertEquals('K5,000 - K20,000', $vendor->getPriceRange());
        $this->assertEquals(0, $vendor->getRating()->getRating());
        $this->assertEquals(0, $vendor->getRating()->getReviewCount());
        $this->assertFalse($vendor->isVerified());
        $this->assertFalse($vendor->isFeatured());
        $this->assertNull($vendor->getServices());
        $this->assertNull($vendor->getPortfolioImages());
        $this->assertNull($vendor->getAvailability());
        $this->assertNotNull($vendor->getCreatedAt());
        $this->assertNotNull($vendor->getUpdatedAt());
    }

    public function test_create_generates_slug(): void
    {
        $vendor = WeddingVendor::create(
            userId: 1,
            businessName: 'Flowers & More, Co',
            category: VendorCategory::flowers(),
            contactPerson: 'Jane',
            phone: '+260970000000',
            email: 'j@flowers.com',
            location: 'Ndola',
            description: 'Florist',
            priceRange: 'K500'
        );

        $this->assertEquals('flowers-and-more-co', $vendor->getSlug());
    }

    public function test_verify(): void
    {
        $vendor = $this->createVendor();
        $vendor->verify();
        $this->assertTrue($vendor->isVerified());
    }

    public function test_unverify(): void
    {
        $vendor = $this->createVendor();
        $vendor->verify();
        $vendor->unverify();
        $this->assertFalse($vendor->isVerified());
    }

    public function test_feature_requires_verified(): void
    {
        $this->expectException(\DomainException::class);
        $vendor = $this->createVendor();
        $vendor->feature();
    }

    public function test_feature_succeeds_when_verified(): void
    {
        $vendor = $this->createVendor();
        $vendor->verify();
        $vendor->feature();
        $this->assertTrue($vendor->isFeatured());
    }

    public function test_unfeature(): void
    {
        $vendor = $this->createVendor();
        $vendor->verify();
        $vendor->feature();
        $vendor->unfeature();
        $this->assertFalse($vendor->isFeatured());
    }

    public function test_update_rating(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateRating(4.5, 10);

        $this->assertEquals(4.5, $vendor->getRating()->getRating());
        $this->assertEquals(10, $vendor->getRating()->getReviewCount());
    }

    public function test_add_portfolio_image(): void
    {
        $vendor = $this->createVendor();
        $vendor->addPortfolioImage('photo1.jpg');
        $vendor->addPortfolioImage('photo2.jpg');

        $this->assertEquals(['photo1.jpg', 'photo2.jpg'], $vendor->getPortfolioImages());
    }

    public function test_remove_portfolio_image(): void
    {
        $vendor = $this->createVendor();
        $vendor->addPortfolioImage('photo1.jpg');
        $vendor->addPortfolioImage('photo2.jpg');
        $vendor->removePortfolioImage('photo1.jpg');

        $this->assertEquals(['photo2.jpg'], array_values($vendor->getPortfolioImages()));
    }

    public function test_remove_portfolio_image_from_empty(): void
    {
        $vendor = $this->createVendor();
        $vendor->removePortfolioImage('nonexistent.jpg');
        $this->assertNull($vendor->getPortfolioImages());
    }

    public function test_update_services(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateServices(['Wedding Planning', 'Coordination']);

        $this->assertEquals(['Wedding Planning', 'Coordination'], $vendor->getServices());
    }

    public function test_update_availability(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateAvailability(['blocked_dates' => ['2026-12-25']]);

        $this->assertEquals(['blocked_dates' => ['2026-12-25']], $vendor->getAvailability());
    }

    public function test_is_available_on_defaults_to_true(): void
    {
        $vendor = $this->createVendor();
        $this->assertTrue($vendor->isAvailableOn(Carbon::now()));
    }

    public function test_is_available_on_blocked_date(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateAvailability(['blocked_dates' => ['2026-12-25']]);
        $this->assertFalse($vendor->isAvailableOn(Carbon::parse('2026-12-25')));
    }

    public function test_is_available_on_non_blocked_date(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateAvailability(['blocked_dates' => ['2026-12-25']]);
        $this->assertTrue($vendor->isAvailableOn(Carbon::parse('2026-12-26')));
    }

    public function test_has_high_rating(): void
    {
        $vendor = $this->createVendor();
        $vendor->updateRating(4.0, 5);
        $this->assertTrue($vendor->hasHighRating());

        $vendor->updateRating(3.9, 5);
        $this->assertFalse($vendor->hasHighRating());
    }

    private function createVendor(): WeddingVendor
    {
        return WeddingVendor::create(
            userId: 1,
            businessName: 'Elegant Events',
            category: VendorCategory::planning(),
            contactPerson: 'John Doe',
            phone: '+260970000000',
            email: 'info@elegantevents.com',
            location: 'Lusaka',
            description: 'Professional wedding planning services',
            priceRange: 'K5,000 - K20,000'
        );
    }
}
