<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowMart\Entities;

use App\Domain\GrowMart\Entities\Product;
use App\Domain\GrowMart\ValueObjects\Money;
use App\Domain\GrowMart\ValueObjects\ProductStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private function makeProduct(array $overrides = []): Product
    {
        return Product::fromArray(array_merge([
            'category_id' => 1,
            'name' => 'Fresh Tomatoes',
            'slug' => 'fresh-tomatoes',
            'description' => 'Locally grown tomatoes',
            'unit' => 'kg',
            'price' => 1500,
            'status' => 'active',
            'total_stock' => 100,
        ], $overrides));
    }

    #[Test]
    public function from_array_hydrates_all_fields(): void
    {
        $product = $this->makeProduct(['id' => 42]);
        $this->assertEquals(42, $product->id());
        $this->assertEquals(1, $product->categoryId());
        $this->assertEquals('Fresh Tomatoes', $product->name());
        $this->assertEquals('fresh-tomatoes', $product->slug());
        $this->assertEquals('Locally grown tomatoes', $product->description());
        $this->assertEquals('kg', $product->unit());
    }

    #[Test]
    public function id_is_null_when_not_provided(): void
    {
        $product = $this->makeProduct();
        $this->assertNull($product->id());
    }

    #[Test]
    public function price_is_money_value_object(): void
    {
        $product = $this->makeProduct();
        $this->assertInstanceOf(Money::class, $product->price());
        $this->assertEquals(1500, $product->price()->ngwee());
    }

    #[Test]
    public function compare_price_is_null_when_not_set(): void
    {
        $product = $this->makeProduct();
        $this->assertNull($product->comparePrice());
    }

    #[Test]
    public function compare_price_is_money_when_set(): void
    {
        $product = $this->makeProduct(['compare_price' => 2000]);
        $this->assertInstanceOf(Money::class, $product->comparePrice());
        $this->assertEquals(2000, $product->comparePrice()->ngwee());
    }

    #[Test]
    public function status_is_product_status_value_object(): void
    {
        $product = $this->makeProduct();
        $this->assertInstanceOf(ProductStatus::class, $product->status());
        $this->assertTrue($product->status()->isActive());
    }

    #[Test]
    public function status_from_string_defaults_to_active(): void
    {
        $product = $this->makeProduct(['status' => 'non_existent']);
        $this->assertTrue($product->status()->isActive());
    }

    #[Test]
    public function is_available_when_active_and_in_stock(): void
    {
        $product = $this->makeProduct(['status' => 'active', 'total_stock' => 10]);
        $this->assertTrue($product->isAvailable());
    }

    #[Test]
    public function is_not_available_when_out_of_stock(): void
    {
        $product = $this->makeProduct(['status' => 'active', 'total_stock' => 0]);
        $this->assertFalse($product->isAvailable());
    }

    #[Test]
    public function is_not_available_when_discontinued(): void
    {
        $product = $this->makeProduct(['status' => 'discontinued', 'total_stock' => 10]);
        $this->assertFalse($product->isAvailable());
    }

    #[Test]
    public function is_not_available_when_out_of_stock_status(): void
    {
        $product = $this->makeProduct(['status' => 'out_of_stock', 'total_stock' => 10]);
        $this->assertFalse($product->isAvailable());
    }

    #[Test]
    public function has_discount_when_compare_price_higher_than_price(): void
    {
        $product = $this->makeProduct(['price' => 1000, 'compare_price' => 1500]);
        $this->assertTrue($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_is_null(): void
    {
        $product = $this->makeProduct();
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_is_equal(): void
    {
        $product = $this->makeProduct(['price' => 1000, 'compare_price' => 1000]);
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function no_discount_when_compare_price_is_lower(): void
    {
        $product = $this->makeProduct(['price' => 1000, 'compare_price' => 800]);
        $this->assertFalse($product->hasDiscount());
    }

    #[Test]
    public function get_discount_percentage_returns_correct_value(): void
    {
        $product = $this->makeProduct(['price' => 1000, 'compare_price' => 2000]);
        $this->assertEquals(50, $product->getDiscountPercentage());
    }

    #[Test]
    public function get_discount_percentage_rounds_correctly(): void
    {
        $product = $this->makeProduct(['price' => 1500, 'compare_price' => 2000]);
        $this->assertEquals(25, $product->getDiscountPercentage());
    }

    #[Test]
    public function get_discount_percentage_is_zero_when_no_compare_price(): void
    {
        $product = $this->makeProduct();
        $this->assertEquals(0, $product->getDiscountPercentage());
    }

    #[Test]
    public function images_defaults_to_empty_array(): void
    {
        $product = $this->makeProduct();
        $this->assertEquals([], $product->images());
    }

    #[Test]
    public function images_returns_provided_array(): void
    {
        $images = [['path' => 'img1.jpg'], ['path' => 'img2.jpg']];
        $product = $this->makeProduct(['images' => $images]);
        $this->assertSame($images, $product->images());
    }

    #[Test]
    public function total_stock_defaults_to_zero(): void
    {
        $product = Product::fromArray([
            'category_id' => 1,
            'name' => 'Test',
            'slug' => 'test',
            'unit' => 'pcs',
            'price' => 500,
        ]);
        $this->assertEquals(0, $product->totalStock());
    }

    #[Test]
    public function total_stock_returns_provided_value(): void
    {
        $product = $this->makeProduct(['total_stock' => 500]);
        $this->assertEquals(500, $product->totalStock());
    }

    #[Test]
    public function from_array_missing_description_defaults_to_empty(): void
    {
        $product = Product::fromArray([
            'category_id' => 1,
            'name' => 'Test',
            'slug' => 'test',
            'unit' => 'pcs',
            'price' => 500,
        ]);
        $this->assertEquals('', $product->description());
    }

    #[Test]
    public function constructor_creates_product_with_all_args(): void
    {
        $product = new Product(
            id: 1,
            categoryId: 2,
            name: 'Organic Apples',
            slug: 'organic-apples',
            description: 'Fresh organic apples',
            unit: 'kg',
            price: Money::fromKwacha(25.00),
            comparePrice: Money::fromKwacha(30.00),
            status: ProductStatus::active(),
            images: [['path' => 'apple.jpg']],
            totalStock: 200,
        );

        $this->assertEquals(1, $product->id());
        $this->assertEquals(2, $product->categoryId());
        $this->assertEquals('Organic Apples', $product->name());
        $this->assertEquals('organic-apples', $product->slug());
        $this->assertEquals('Fresh organic apples', $product->description());
        $this->assertEquals('kg', $product->unit());
        $this->assertEquals(2500, $product->price()->ngwee());
        $this->assertEquals(3000, $product->comparePrice()->ngwee());
        $this->assertTrue($product->status()->isActive());
        $this->assertCount(1, $product->images());
        $this->assertEquals(200, $product->totalStock());
    }

    #[Test]
    public function round_trip_from_array_to_array(): void
    {
        $data = [
            'id' => 10,
            'category_id' => 3,
            'name' => 'Honey',
            'slug' => 'honey',
            'description' => 'Pure honey',
            'unit' => 'bottle',
            'price' => 5000,
            'compare_price' => 6000,
            'status' => 'active',
            'images' => [['path' => 'honey.jpg']],
            'total_stock' => 50,
        ];

        $product = Product::fromArray($data);
        $this->assertEquals(10, $product->id());
        $this->assertEquals(3, $product->categoryId());
        $this->assertEquals('Honey', $product->name());
        $this->assertEquals('honey', $product->slug());
        $this->assertEquals('Pure honey', $product->description());
        $this->assertEquals('bottle', $product->unit());
        $this->assertEquals(5000, $product->price()->ngwee());
        $this->assertEquals(6000, $product->comparePrice()->ngwee());
        $this->assertTrue($product->status()->isActive());
        $this->assertCount(1, $product->images());
        $this->assertEquals(50, $product->totalStock());
    }
}
