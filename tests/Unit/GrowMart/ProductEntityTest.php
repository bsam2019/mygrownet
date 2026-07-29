<?php

namespace Tests\Unit\GrowMart;

use App\Domain\GrowMart\Entities\Product;
use App\Domain\GrowMart\ValueObjects\Money;
use App\Domain\GrowMart\ValueObjects\ProductStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductEntityTest extends TestCase
{
    private array $baseData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseData = [
            'category_id' => 1,
            'name' => 'Fresh Tomatoes',
            'slug' => 'fresh-tomatoes',
            'description' => 'Locally grown tomatoes',
            'unit' => 'kg',
            'price' => 1500,
            'status' => 'active',
            'total_stock' => 100,
        ];
    }

    private function create(array $overrides = []): Product
    {
        return Product::fromArray(array_merge($this->baseData, $overrides));
    }

    #[Test]
    public function from_array_hydrates_correctly(): void
    {
        $product = $this->create(['id' => 42]);
        $this->assertEquals(42, $product->id());
        $this->assertEquals(1, $product->categoryId());
        $this->assertEquals('Fresh Tomatoes', $product->name());
        $this->assertEquals('fresh-tomatoes', $product->slug());
        $this->assertEquals('Locally grown tomatoes', $product->description());
        $this->assertEquals('kg', $product->unit());
    }

    #[Test]
    public function price_is_money_object(): void
    {
        $product = $this->create();
        $this->assertInstanceOf(Money::class, $product->price());
        $this->assertEquals(1500, $product->price()->ngwee());
    }

    #[Test]
    public function compare_price_is_null_when_not_set(): void
    {
        $product = $this->create();
        $this->assertNull($product->comparePrice());
    }

    #[Test]
    public function compare_price_is_money_when_set(): void
    {
        $product = $this->create(['compare_price' => 2000]);
        $this->assertInstanceOf(Money::class, $product->comparePrice());
        $this->assertEquals(2000, $product->comparePrice()->ngwee());
    }

    #[Test]
    public function status_is_product_status_object(): void
    {
        $product = $this->create();
        $this->assertInstanceOf(ProductStatus::class, $product->status());
        $this->assertTrue($product->status()->isActive());
    }

    #[Test]
    public function is_available_when_active_and_in_stock(): void
    {
        $product = $this->create(['status' => 'active', 'total_stock' => 10]);
        $this->assertTrue($product->isAvailable());
    }

    #[Test]
    public function is_not_available_when_out_of_stock(): void
    {
        $product = $this->create(['status' => 'active', 'total_stock' => 0]);
        $this->assertFalse($product->isAvailable());
    }

    #[Test]
    public function is_not_available_when_discontinued(): void
    {
        $product = $this->create(['status' => 'discontinued', 'total_stock' => 10]);
        $this->assertFalse($product->isAvailable());
    }

    #[Test]
    public function has_discount_when_compare_price_higher(): void
    {
        $product = $this->create(['price' => 1000, 'compare_price' => 1500]);
        $this->assertTrue($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_null(): void
    {
        $product = $this->create();
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_lower(): void
    {
        $product = $this->create(['price' => 1000, 'compare_price' => 800]);
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function discount_percentage_calculated_correctly(): void
    {
        $product = $this->create(['price' => 1000, 'compare_price' => 2000]);
        $this->assertEquals(50, $product->getDiscountPercentage());
    }

    #[Test]
    public function discount_percentage_zero_when_no_discount(): void
    {
        $product = $this->create();
        $this->assertEquals(0, $product->getDiscountPercentage());
    }

    #[Test]
    public function images_defaults_to_empty_array(): void
    {
        $product = $this->create();
        $this->assertEquals([], $product->images());
    }

    #[Test]
    public function total_stock_defaults_to_zero(): void
    {
        $product = $this->create([]);
        $this->assertEquals(100, $product->totalStock());
    }
}
