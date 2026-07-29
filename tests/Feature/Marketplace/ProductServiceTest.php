<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Services\ProductService;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceCategory;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;
    private int $sellerId;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();

        $seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Test Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved',
            'kyc_documents' => [],
            'total_orders' => 0,
            'completed_orders' => 0,
            'total_sales_amount' => 0,
            'dispute_rate' => 0,
            'cancellation_rate' => 0,
            'rating' => 0,
            'is_active' => true,
            'commission_rate' => 10.0,
            'phone' => '0977000000',
            'email' => 'test@example.com',
        ]);
        $this->sellerId = $seller->id;

        $category = MarketplaceCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $this->categoryId = $category->id;

        $this->service = new ProductService(
            new EloquentProductRepository(),
            new EloquentCategoryRepository(),
        );
    }

    public function test_create_product(): void
    {
        $result = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 15000,
            'stock_quantity' => 10,
        ]);

        $this->assertNotNull($result['id']);
        $this->assertEquals('Test Product', $result['name']);
        $this->assertEquals('test-product', $result['slug']);
        $this->assertEquals(15000, $result['price']);
        $this->assertEquals(10, $result['stock_quantity']);
        $this->assertEquals('pending', $result['status']);
    }

    public function test_create_product_generates_unique_slug(): void
    {
        $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test Product',
            'description' => 'First',
            'price' => 10000,
        ]);

        $result = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test Product',
            'description' => 'Second',
            'price' => 20000,
        ]);

        $this->assertEquals('test-product-1', $result['slug']);
    }

    public function test_getById_returns_product(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
        ]);

        $result = $this->service->getById($created['id']);
        $this->assertNotNull($result);
        $this->assertEquals($created['id'], $result['id']);
    }

    public function test_getBySlug_returns_product(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
        ]);

        $result = $this->service->getBySlug('test');
        $this->assertNotNull($result);
        $this->assertEquals($created['id'], $result['id']);
    }

    public function test_update_product(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Original',
            'description' => 'Original desc',
            'price' => 10000,
        ]);

        $updated = $this->service->update($created['id'], [
            'name' => 'Updated',
            'price' => 20000,
        ]);

        $this->assertEquals('Updated', $updated['name']);
        $this->assertEquals(20000, $updated['price']);
    }

    public function test_delete_product(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
        ]);

        $this->service->delete($created['id']);
        $this->assertNull($this->service->getById($created['id']));
    }

    public function test_approveProduct(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
        ]);

        $this->service->approveProduct($created['id']);
        $result = $this->service->getById($created['id']);
        $this->assertEquals('active', $result['status']);
    }

    public function test_rejectProduct(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
        ]);

        $this->service->rejectProduct($created['id'], 'Policy violation');
        $result = $this->service->getById($created['id']);
        $this->assertEquals('rejected', $result['status']);
    }

    public function test_stock_operations(): void
    {
        $created = $this->service->create($this->sellerId, [
            'category_id' => $this->categoryId,
            'name' => 'Test',
            'description' => 'Desc',
            'price' => 10000,
            'stock_quantity' => 10,
        ]);

        $this->service->decrementStock($created['id'], 3);
        $this->assertEquals(7, $this->service->getById($created['id'])['stock_quantity']);

        $this->service->incrementStock($created['id'], 5);
        $this->assertEquals(12, $this->service->getById($created['id'])['stock_quantity']);
    }

    public function test_getFeaturedProducts(): void
    {
        $p1 = $this->service->create($this->sellerId, ['category_id' => $this->categoryId, 'name' => 'P1', 'description' => 'D1', 'price' => 10000]);
        $this->service->approveProduct($p1['id']);
        $p2 = $this->service->create($this->sellerId, ['category_id' => $this->categoryId, 'name' => 'P2', 'description' => 'D2', 'price' => 20000]);
        $this->service->approveProduct($p2['id']);

        $featured = $this->service->getFeaturedProducts(2);
        $this->assertEmpty($featured); // No products are featured
    }

    public function test_getCategories(): void
    {
        MarketplaceCategory::create(['name' => 'Books', 'slug' => 'books', 'is_active' => true]);

        $categories = $this->service->getCategories();
        $this->assertCount(2, $categories); // electronics from setUp + books
    }

    public function test_getBySeller_filters(): void
    {
        $this->service->create($this->sellerId, ['category_id' => $this->categoryId, 'name' => 'P1', 'description' => 'D1', 'price' => 10000]);
        $this->service->create($this->sellerId, ['category_id' => $this->categoryId, 'name' => 'P2', 'description' => 'D2', 'price' => 20000]);

        $products = $this->service->getBySeller($this->sellerId);
        $this->assertCount(2, $products);
    }
}
