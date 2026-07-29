<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Domain\Marketplace\ValueObjects\ProductStatus;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\Marketplace\MarketplaceProduct;
use App\Models\Marketplace\MarketplaceSeller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repo;
    private MarketplaceSeller $seller;
    private MarketplaceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(ProductRepositoryInterface::class);
        $user = User::factory()->create();
        $this->seller = MarketplaceSeller::create([
            'user_id' => $user->id,
            'business_name' => 'Test Shop',
            'business_type' => 'retail',
            'province' => 'Lusaka',
            'district' => 'Lusaka',
            'trust_level' => 'new',
            'kyc_status' => 'approved',
            'kyc_documents' => [],
            'is_active' => true,
        ]);
        $this->category = MarketplaceCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);
    }

    #[Test]
    public function finds_by_id(): void
    {
        $product = MarketplaceProduct::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A test',
            'price' => 10000,
            'stock_quantity' => 10,
            'images' => ['img.jpg'],
            'status' => 'active',
        ]);

        $found = $this->repo->findById($product->id);
        $this->assertNotNull($found);
        $this->assertEquals('Test Product', $found->name);
        $this->assertEquals(10000, $found->price->amount());
    }

    #[Test]
    public function finds_by_slug(): void
    {
        MarketplaceProduct::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A test',
            'price' => 10000,
            'stock_quantity' => 10,
            'images' => [],
            'status' => 'active',
        ]);

        $found = $this->repo->findBySlug('test-product');
        $this->assertNotNull($found);
    }

    #[Test]
    public function save_creates_product(): void
    {
        $product = new \App\Domain\Marketplace\Entities\Product(
            id: null,
            sellerId: $this->seller->id,
            categoryId: $this->category->id,
            name: 'New Product',
            slug: 'new-product',
            description: 'Brand new',
            price: Money::fromKwacha(50.00),
            comparePrice: null,
            stockQuantity: 25,
            images: ['img.jpg'],
            status: ProductStatus::draft(),
            isFeatured: false,
        );

        $result = $this->repo->save($product);
        $this->assertNotNull($result->id);
        $this->assertEquals('New Product', $result->name);
        $this->assertTrue($result->status->isDraft());
    }

    #[Test]
    public function update_status(): void
    {
        $product = MarketplaceProduct::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Test',
            'slug' => 'test',
            'description' => '',
            'price' => 1000,
            'stock_quantity' => 5,
            'images' => [],
            'status' => 'pending',
        ]);

        $this->repo->updateStatus($product->id, 'active');
        $this->assertDatabaseHas('marketplace_products', ['id' => $product->id, 'status' => 'active']);
    }

    #[Test]
    public function stock_operations(): void
    {
        $product = MarketplaceProduct::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Stock Test',
            'slug' => 'stock-test',
            'description' => '',
            'price' => 1000,
            'stock_quantity' => 10,
            'images' => [],
            'status' => 'active',
        ]);

        $this->repo->decrementStock($product->id, 3);
        $this->assertDatabaseHas('marketplace_products', ['id' => $product->id, 'stock_quantity' => 7]);

        $this->repo->incrementStock($product->id, 5);
        $this->assertDatabaseHas('marketplace_products', ['id' => $product->id, 'stock_quantity' => 12]);
    }

    #[Test]
    public function slug_exists_checks(): void
    {
        MarketplaceProduct::create([
            'seller_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'name' => 'Test',
            'slug' => 'taken-slug',
            'description' => '',
            'price' => 1000,
            'stock_quantity' => 5,
            'images' => [],
            'status' => 'draft',
        ]);

        $this->assertTrue($this->repo->slugExists('taken-slug'));
        $this->assertFalse($this->repo->slugExists('free-slug'));
    }

    #[Test]
    public function find_active_filters_by_status(): void
    {
        MarketplaceProduct::create([
            'seller_id' => $this->seller->id, 'category_id' => $this->category->id,
            'name' => 'Active 1', 'slug' => 'active-1', 'description' => '', 'price' => 1000,
            'stock_quantity' => 5, 'images' => [], 'status' => 'active',
        ]);
        MarketplaceProduct::create([
            'seller_id' => $this->seller->id, 'category_id' => $this->category->id,
            'name' => 'Draft', 'slug' => 'draft-1', 'description' => '', 'price' => 1000,
            'stock_quantity' => 5, 'images' => [], 'status' => 'draft',
        ]);

        $active = $this->repo->findActive();
        $this->assertCount(1, $active);
    }
}
