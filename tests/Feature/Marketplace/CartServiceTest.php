<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Entities\Product;
use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\Services\CartService;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Domain\Marketplace\ValueObjects\ProductStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;
    private $productRepo;

    protected function setUp(): void
    {
        parent::setUp();
        Session::start();

        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->cartService = new CartService($this->productRepo);
    }

    private function makeProduct(int $id, int $stock = 10, int $price = 10000, string $status = 'active'): Product
    {
        return new Product(
            id: $id,
            sellerId: 1,
            categoryId: 1,
            name: "Product $id",
            slug: "product-$id",
            description: 'Test',
            price: Money::fromNgwee($price),
            comparePrice: null,
            stockQuantity: $stock,
            images: [],
            status: ProductStatus::fromString($status),
            isFeatured: false,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function test_getCart_returns_empty_array_when_empty(): void
    {
        $this->assertEmpty($this->cartService->getCart());
    }

    public function test_addItem_adds_to_cart(): void
    {
        $product = $this->makeProduct(1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $summary = $this->cartService->addItem(1);

        $this->assertCount(1, $summary['items']);
        $this->assertEquals(1, $summary['item_count']);
        $this->assertEquals(10000, $summary['subtotal']);
    }

    public function test_addItem_throws_when_unavailable(): void
    {
        $product = $this->makeProduct(1, stock: 0, status: 'suspended');
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product is not available.');

        $this->cartService->addItem(1);
    }

    public function test_addItem_throws_when_insufficient_stock(): void
    {
        $product = $this->makeProduct(1, stock: 1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->expectException(\Exception::class);

        $this->cartService->addItem(1, 5);
    }

    public function test_updateQuantity_updates_existing_item(): void
    {
        $product = $this->makeProduct(1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->cartService->addItem(1);
        $summary = $this->cartService->updateQuantity(1, 3);

        $this->assertEquals(3, $summary['items'][0]['quantity']);
        $this->assertEquals(30000, $summary['subtotal']);
    }

    public function test_updateQuantity_removes_item_when_zero(): void
    {
        $product = $this->makeProduct(1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->cartService->addItem(1);
        $summary = $this->cartService->updateQuantity(1, 0);

        $this->assertCount(0, $summary['items']);
    }

    public function test_updateQuantity_throws_when_not_in_cart(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Item not in cart.');

        $this->cartService->updateQuantity(999, 1);
    }

    public function test_removeItem_removes_from_cart(): void
    {
        $product = $this->makeProduct(1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->cartService->addItem(1);
        $summary = $this->cartService->removeItem(1);

        $this->assertCount(0, $summary['items']);
    }

    public function test_clearCart_empties_cart(): void
    {
        $product = $this->makeProduct(1);
        $this->productRepo->method('findById')->with(1)->willReturn($product);

        $this->cartService->addItem(1);
        $this->cartService->clearCart();

        $this->assertEmpty($this->cartService->getCart());
    }

    public function test_getCartForCheckout_filters_unavailable(): void
    {
        $active = $this->makeProduct(1, stock: 5);
        $outOfStock = $this->makeProduct(2, stock: 0);

        $this->productRepo->method('findById')->willReturnCallback(fn($id) => match ($id) {
            1 => $active,
            2 => $outOfStock,
            default => null,
        });

        $this->cartService->addItem(1);
        Session::put('marketplace_cart', [
            '1' => ['product_id' => 1, 'seller_id' => 1, 'name' => 'P1', 'price' => 10000, 'quantity' => 1, 'max_quantity' => 5],
            '2' => ['product_id' => 2, 'seller_id' => 1, 'name' => 'P2', 'price' => 5000, 'quantity' => 1, 'max_quantity' => 0],
        ]);

        $checkout = $this->cartService->getCartForCheckout();

        $this->assertCount(1, $checkout);
        $this->assertEquals(1, $checkout[0]['product_id']);
    }

    public function test_getCartSummary_returns_multi_seller_info(): void
    {
        $product1 = new Product(
            id: 1, sellerId: 1, categoryId: 1, name: 'P1', slug: 'p1',
            description: 'Test', price: Money::fromNgwee(10000), comparePrice: null,
            stockQuantity: 10, images: [], status: ProductStatus::active(),
            isFeatured: false, createdAt: new \DateTimeImmutable(),
        );
        $product2 = new Product(
            id: 2, sellerId: 2, categoryId: 1, name: 'P2', slug: 'p2',
            description: 'Test', price: Money::fromNgwee(20000), comparePrice: null,
            stockQuantity: 5, images: [], status: ProductStatus::active(),
            isFeatured: false, createdAt: new \DateTimeImmutable(),
        );

        $this->productRepo->method('findById')->willReturnCallback(fn($id) => match ($id) {
            1 => $product1,
            2 => $product2,
            default => null,
        });

        $this->cartService->addItem(1);
        $this->cartService->addItem(2);

        $summary = $this->cartService->getCartSummary();
        $this->assertTrue($summary['is_multi_seller']);
        $this->assertEquals(2, $summary['seller_count']);
    }
}
