<?php

namespace Tests\Unit\Marketplace;

use App\Domain\Marketplace\Entities\Product;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Domain\Marketplace\ValueObjects\ProductStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductEntityTest extends TestCase
{
    private function makeProduct(array $overrides = []): Product
    {
        $params = array_merge([
            'id' => 1,
            'sellerId' => 10,
            'categoryId' => 5,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A test product',
            'price' => Money::fromKwacha(100.00),
            'comparePrice' => null,
            'stockQuantity' => 50,
            'images' => ['image1.jpg'],
            'status' => ProductStatus::active(),
            'isFeatured' => false,
        ], $overrides);

        return new Product(...$params);
    }

    #[Test]
    public function is_available_when_active_and_in_stock(): void
    {
        $this->assertTrue($this->makeProduct()->isAvailable());
    }

    #[Test]
    public function not_available_when_draft(): void
    {
        $this->assertFalse($this->makeProduct(['status' => ProductStatus::draft()])->isAvailable());
    }

    #[Test]
    public function not_available_when_out_of_stock(): void
    {
        $this->assertFalse($this->makeProduct(['stockQuantity' => 0])->isAvailable());
    }

    #[Test]
    public function has_discount_when_compare_price_higher(): void
    {
        $product = $this->makeProduct(['comparePrice' => Money::fromKwacha(150.00)]);
        $this->assertTrue($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_lower(): void
    {
        $product = $this->makeProduct(['comparePrice' => Money::fromKwacha(80.00)]);
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_no_compare_price(): void
    {
        $this->assertFalse($this->makeProduct()->hasDiscount());
    }

    #[Test]
    public function discount_percentage_is_correct(): void
    {
        $product = $this->makeProduct([
            'price' => Money::fromKwacha(75.00),
            'comparePrice' => Money::fromKwacha(100.00),
        ]);
        $this->assertEquals(25, $product->getDiscountPercentage());
    }

    #[Test]
    public function can_be_purchased_with_sufficient_stock(): void
    {
        $this->assertTrue($this->makeProduct()->canBePurchased(10));
    }

    #[Test]
    public function cannot_be_purchased_with_insufficient_stock(): void
    {
        $this->assertFalse($this->makeProduct()->canBePurchased(100));
    }

    #[Test]
    public function toArray_returns_all_fields(): void
    {
        $product = $this->makeProduct();
        $data = $product->toArray();
        $this->assertEquals('Test Product', $data['name']);
        $this->assertEquals(10000, $data['price']);
        $this->assertEquals('active', $data['status']);
    }
}
