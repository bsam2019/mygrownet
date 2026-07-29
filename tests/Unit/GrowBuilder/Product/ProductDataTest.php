<?php

namespace Tests\Unit\GrowBuilder\Product;

use App\Domain\GrowBuilder\Product\DTOs\ProductData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductDataTest extends TestCase
{
    #[Test]
    public function it_creates_with_all_fields(): void
    {
        $dto = new ProductData(
            name: 'Test Product',
            description: 'A product description',
            shortDescription: 'Short desc',
            priceInNgwee: 15000,
            comparePriceInNgwee: 20000,
            stockQuantity: 10,
            trackStock: true,
            sku: 'TST-001',
            category: 'Electronics',
            images: ['img1.jpg', 'img2.jpg'],
            isActive: true,
            isFeatured: true,
        );

        $this->assertEquals('Test Product', $dto->name);
        $this->assertEquals('A product description', $dto->description);
        $this->assertEquals('Short desc', $dto->shortDescription);
        $this->assertEquals(15000, $dto->priceInNgwee);
        $this->assertEquals(20000, $dto->comparePriceInNgwee);
        $this->assertEquals(10, $dto->stockQuantity);
        $this->assertTrue($dto->trackStock);
        $this->assertEquals('TST-001', $dto->sku);
        $this->assertEquals('Electronics', $dto->category);
        $this->assertEquals(['img1.jpg', 'img2.jpg'], $dto->images);
        $this->assertTrue($dto->isActive);
        $this->assertTrue($dto->isFeatured);
    }

    #[Test]
    public function from_request_converts_prices_to_ngwee(): void
    {
        $data = [
            'name' => 'Product',
            'price' => 150.00,
            'compare_price' => 200.00,
            'stock_quantity' => 5,
        ];

        $dto = ProductData::fromRequest($data);

        $this->assertEquals(15000, $dto->priceInNgwee);
        $this->assertEquals(20000, $dto->comparePriceInNgwee);
        $this->assertEquals('Product', $dto->name);
        $this->assertNull($dto->description);
        $this->assertTrue($dto->trackStock);
        $this->assertTrue($dto->isActive);
        $this->assertFalse($dto->isFeatured);
    }

    #[Test]
    public function from_request_with_minimal_data(): void
    {
        $dto = ProductData::fromRequest(['name' => 'Minimal']);

        $this->assertEquals('Minimal', $dto->name);
        $this->assertEquals(0, $dto->priceInNgwee);
        $this->assertNull($dto->comparePriceInNgwee);
        $this->assertEquals(0, $dto->stockQuantity);
        $this->assertTrue($dto->trackStock);
        $this->assertEquals([], $dto->images);
        $this->assertTrue($dto->isActive);
    }

    #[Test]
    public function to_array_returns_correct_format(): void
    {
        $dto = ProductData::fromRequest([
            'name' => 'Test',
            'price' => 99.99,
            'stock_quantity' => 3,
        ]);

        $result = $dto->toArray();

        $this->assertEquals('Test', $result['name']);
        $this->assertEquals(9999, $result['price']);
        $this->assertEquals(3, $result['stock_quantity']);
        $this->assertTrue($result['track_stock']);
        $this->assertArrayHasKey('images', $result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('sku', $result);
    }
}
