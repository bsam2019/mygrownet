<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Product;
use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ProductEntityTest extends TestCase
{
    public function test_create_with_minimal_params(): void
    {
        $product = Product::create(
            siteId: 1,
            name: 'Test Product',
            slug: 'test-product',
            priceInNgwee: 15000,
            description: 'A test product',
            stockQuantity: 10,
        );

        $this->assertNull($product->getId());
        $this->assertEquals(1, $product->getSiteId());
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals('test-product', $product->getSlug());
        $this->assertEquals(15000, $product->getPrice()->getAmountInNgwee());
        $this->assertEquals('A test product', $product->getDescription());
        $this->assertEquals(10, $product->getStockQuantity());
        $this->assertTrue($product->isTrackingStock());
        $this->assertTrue($product->isActive());
        $this->assertFalse($product->isFeatured());
        $this->assertNull($product->getComparePrice());
        $this->assertNull($product->getShortDescription());
        $this->assertNull($product->getSku());
        $this->assertNull($product->getCategory());
        $this->assertNull($product->getWeight());
        $this->assertEquals([], $product->getImages());
        $this->assertEquals([], $product->getVariants());
        $this->assertEquals([], $product->getAttributes());
        $this->assertEquals(0, $product->getSortOrder());
    }

    public function test_reconstitute(): void
    {
        $now = new DateTimeImmutable();
        $product = Product::reconstitute(
            id: ProductId::fromInt(5),
            siteId: 1, name: 'Recon', slug: 'recon',
            description: 'Recon desc', shortDescription: 'Short',
            price: Money::fromNgwee(1000), comparePrice: Money::fromNgwee(2000),
            images: ['img.jpg'],
            stockQuantity: 50, trackStock: true,
            sku: 'SKU-001', category: 'Electronics',
            variants: [['color' => 'red']], attributes: ['size' => 'M'],
            weight: 1.5, isActive: false, isFeatured: true,
            sortOrder: 3,
            createdAt: $now, updatedAt: $now,
        );

        $this->assertEquals(5, $product->getId()->value());
        $this->assertEquals('Recon', $product->getName());
        $this->assertEquals(1000, $product->getPrice()->getAmountInNgwee());
        $this->assertEquals(2000, $product->getComparePrice()->getAmountInNgwee());
        $this->assertEquals('SKU-001', $product->getSku());
        $this->assertEquals('Electronics', $product->getCategory());
        $this->assertEquals(1.5, $product->getWeight());
        $this->assertFalse($product->isActive());
        $this->assertTrue($product->isFeatured());
        $this->assertEquals(3, $product->getSortOrder());
        $this->assertEquals('img.jpg', $product->getMainImage());
        $this->assertTrue($product->hasDiscount());
    }

    public function test_normalize_slug(): void
    {
        // Accessing private normalizeSlug via reflection on create
        $product = Product::create(1, 'My Product', '  My Product!!  ', 1000);
        $this->assertEquals('my-product', $product->getSlug());
    }

    public function test_slug_converts_spaces_and_special_chars(): void
    {
        $product = Product::create(1, 'Bold', 'Hello World & More!', 1000);
        $this->assertEquals('hello-world-more', $product->getSlug());
    }

    public function test_update_details(): void
    {
        $product = Product::create(1, 'Old', 'old', 1000);
        $product->updateDetails('New', 'New desc', 'Short', 'Category');
        $this->assertEquals('New', $product->getName());
        $this->assertEquals('New desc', $product->getDescription());
        $this->assertEquals('Short', $product->getShortDescription());
        $this->assertEquals('Category', $product->getCategory());
    }

    public function test_update_pricing(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $product->updatePricing(2000, 3000);

        $this->assertEquals(2000, $product->getPrice()->getAmountInNgwee());
        $this->assertEquals(3000, $product->getComparePrice()->getAmountInNgwee());
        $this->assertTrue($product->hasDiscount());
    }

    public function test_update_pricing_without_compare(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $product->updatePricing(2500);
        $this->assertEquals(2500, $product->getPrice()->getAmountInNgwee());
        $this->assertNull($product->getComparePrice());
    }

    public function test_update_stock(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 10);
        $product->updateStock(5);
        $this->assertEquals(5, $product->getStockQuantity());
    }

    public function test_update_stock_never_negative(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 3);
        $product->updateStock(-10);
        $this->assertEquals(0, $product->getStockQuantity());
    }

    public function test_decrement_stock(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 10);
        $product->decrementStock(3);
        $this->assertEquals(7, $product->getStockQuantity());
    }

    public function test_decrement_stock_does_not_go_below_zero(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 5);
        $product->decrementStock(10);
        $this->assertEquals(0, $product->getStockQuantity());
    }

    public function test_decrement_stock_when_not_tracking(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 10);
        $reflect = new \ReflectionClass($product);
        $trackStockProp = $reflect->getProperty('trackStock');
        $trackStockProp->setAccessible(true);
        $trackStockProp->setValue($product, false);

        $product->decrementStock(5);
        $this->assertEquals(10, $product->getStockQuantity()); // unchanged
    }

    public function test_increment_stock(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 5);
        $product->incrementStock(10);
        $this->assertEquals(15, $product->getStockQuantity());
    }

    public function test_increment_stock_when_not_tracking(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 5);
        $reflect = new \ReflectionClass($product);
        $trackStockProp = $reflect->getProperty('trackStock');
        $trackStockProp->setAccessible(true);
        $trackStockProp->setValue($product, false);

        $product->incrementStock(10);
        $this->assertEquals(5, $product->getStockQuantity()); // unchanged
    }

    public function test_set_images(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $product->setImages(['img1.jpg', 'img2.jpg']);
        $this->assertEquals(['img1.jpg', 'img2.jpg'], $product->getImages());
        $this->assertEquals('img1.jpg', $product->getMainImage());
    }

    public function test_main_image_returns_null_when_empty(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $this->assertNull($product->getMainImage());
    }

    public function test_activate_and_deactivate(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $product->deactivate();
        $this->assertFalse($product->isActive());

        $product->activate();
        $this->assertTrue($product->isActive());
    }

    public function test_set_featured(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000);
        $product->setFeatured(true);
        $this->assertTrue($product->isFeatured());
    }

    public function test_is_in_stock_when_not_tracking(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 0);
        $reflect = new \ReflectionClass($product);
        $prop = $reflect->getProperty('trackStock');
        $prop->setAccessible(true);
        $prop->setValue($product, false);
        $this->assertTrue($product->isInStock());
    }

    public function test_is_in_stock_true_with_stock(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 5);
        $this->assertTrue($product->isInStock());
    }

    public function test_is_in_stock_false_when_zero(): void
    {
        $product = Product::create(1, 'Test', 'test', 1000, stockQuantity: 0);
        $this->assertFalse($product->isInStock());
    }

    public function test_has_discount(): void
    {
        $product = Product::create(1, 'Test', 'test', 1500);
        $product->updatePricing(1500, 2000);
        $this->assertTrue($product->hasDiscount());
    }

    public function test_no_discount_without_compare_price(): void
    {
        $product = Product::create(1, 'Test', 'test', 1500);
        $this->assertFalse($product->hasDiscount());
    }

    public function test_no_discount_when_compare_lower(): void
    {
        $product = Product::create(1, 'Test', 'test', 2000);
        $product->updatePricing(2000, 1000);
        $this->assertFalse($product->hasDiscount());
    }

    public function test_get_discount_percentage(): void
    {
        $product = Product::create(1, 'Test', 'test', 2000);
        $product->updatePricing(1500, 2000);
        $this->assertEquals(25, $product->getDiscountPercentage());
    }

    public function test_get_discount_percentage_returns_0_when_no_discount(): void
    {
        $product = Product::create(1, 'Test', 'test', 1500);
        $this->assertEquals(0, $product->getDiscountPercentage());
    }

    public function test_get_discount_percentage_rounds(): void
    {
        $product = Product::create(1, 'Test', 'test', 1999);
        $product->updatePricing(999, 1999);
        $this->assertEquals(50, $product->getDiscountPercentage());
    }
}
