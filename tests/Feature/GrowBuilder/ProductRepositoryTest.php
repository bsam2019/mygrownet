<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Product;
use App\Domain\GrowBuilder\Repositories\ProductRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repository;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductRepositoryInterface::class);
        $user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $user->id,
            'name' => 'Shop',
            'subdomain' => 'shop',
            'status' => 'published',
            'plan' => 'business',
        ]);
    }

    public function test_save_and_find_by_id(): void
    {
        $product = Product::create($this->site->id, 'Widget', 'widget', 5000);
        $saved = $this->repository->save($product);

        $this->assertNotNull($saved->getId());

        $found = $this->repository->findById($saved->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Widget', $found->getName());
        $this->assertEquals(5000, $found->getPrice()->getAmountInNgwee());
    }

    public function test_find_by_id_for_site(): void
    {
        $product = Product::create($this->site->id, 'Widget', 'widget', 5000);
        $saved = $this->repository->save($product);

        $found = $this->repository->findByIdForSite($saved->getId(), SiteId::fromInt($this->site->id));
        $this->assertNotNull($found);

        $otherSiteId = SiteId::fromInt(9999);
        $notFound = $this->repository->findByIdForSite($saved->getId(), $otherSiteId);
        $this->assertNull($notFound);
    }

    public function test_find_by_slug_for_site(): void
    {
        $product = Product::create($this->site->id, 'Cool Product', 'cool-product', 10000);
        $this->repository->save($product);

        $found = $this->repository->findBySlugForSite(SiteId::fromInt($this->site->id), 'cool-product');
        $this->assertNotNull($found);
        $this->assertEquals('Cool Product', $found->getName());
    }

    public function test_get_all_for_site(): void
    {
        $this->repository->save(Product::create($this->site->id, 'A', 'a', 1000));
        $this->repository->save(Product::create($this->site->id, 'B', 'b', 2000));

        $products = $this->repository->getAllForSite(SiteId::fromInt($this->site->id));
        $this->assertCount(2, $products);
    }

    public function test_get_all_paginated(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->save(Product::create($this->site->id, "P{$i}", "p{$i}", 1000));
        }

        $paginator = $this->repository->getAllForSitePaginated(SiteId::fromInt($this->site->id), 2);
        $this->assertEquals(5, $paginator->total());
        $this->assertCount(2, $paginator->items());
    }

    public function test_get_active_for_site(): void
    {
        $p1 = Product::create($this->site->id, 'Active', 'active', 1000);
        $this->repository->save($p1);

        $p2 = Product::create($this->site->id, 'Inactive', 'inactive', 1000);
        $p2->deactivate();
        $this->repository->save($p2);

        $active = $this->repository->getActiveForSite(SiteId::fromInt($this->site->id));
        $this->assertCount(1, $active);
        $this->assertEquals('Active', $active[0]->getName());
    }

    public function test_get_categories(): void
    {
        $p1 = Product::create($this->site->id, 'A', 'a', 1000);
        $ref = new \ReflectionClass($p1);
        $catProp = $ref->getProperty('category');
        $catProp->setAccessible(true);
        $catProp->setValue($p1, 'Electronics');
        $this->repository->save($p1);

        $p2 = Product::create($this->site->id, 'B', 'b', 2000);
        $catProp->setValue($p2, 'Clothing');
        $this->repository->save($p2);

        $categories = $this->repository->getCategoriesForSite(SiteId::fromInt($this->site->id));
        $this->assertContains('Electronics', $categories);
        $this->assertContains('Clothing', $categories);
    }

    public function test_count(): void
    {
        $this->repository->save(Product::create($this->site->id, 'A', 'a', 1000));
        $this->repository->save(Product::create($this->site->id, 'B', 'b', 2000));

        $this->assertEquals(2, $this->repository->countForSite(SiteId::fromInt($this->site->id)));
    }

    public function test_save_updates_and_preserves_pricing(): void
    {
        $product = Product::create($this->site->id, 'Test', 'test', 5000);
        $saved = $this->repository->save($product);

        $saved->updatePricing(7500, 10000);
        $this->repository->save($saved);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals(7500, $found->getPrice()->getAmountInNgwee());
        $this->assertEquals(10000, $found->getComparePrice()->getAmountInNgwee());
    }

    public function test_generate_unique_slug(): void
    {
        $slug = $this->repository->generateUniqueSlug($this->site->id, 'Test Product');
        $this->assertEquals('test-product', $slug);

        $product = Product::create($this->site->id, 'Test Product', 'test-product', 1000);
        $this->repository->save($product);

        $slug2 = $this->repository->generateUniqueSlug($this->site->id, 'Test Product');
        $this->assertEquals('test-product-2', $slug2);
    }

    public function test_delete(): void
    {
        $product = Product::create($this->site->id, 'Delete', 'delete', 1000);
        $saved = $this->repository->save($product);

        $this->repository->delete($saved->getId());

        $this->assertNull($this->repository->findById($saved->getId()));
    }

    public function test_has_stock(): void
    {
        $product = Product::create($this->site->id, 'Stock', 'stock', 1000, stockQuantity: 10);
        $saved = $this->repository->save($product);

        $this->assertTrue($this->repository->hasStock($saved->getId(), 5));
        $this->assertTrue($this->repository->hasStock($saved->getId(), 10));
        $this->assertFalse($this->repository->hasStock($saved->getId(), 11));
    }

    public function test_decrement_stock(): void
    {
        $product = Product::create($this->site->id, 'Decr', 'decr', 1000, stockQuantity: 10);
        $saved = $this->repository->save($product);

        $this->repository->decrementStock($saved->getId(), 3);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals(7, $found->getStockQuantity());
    }

    public function test_decrement_stock_below_zero_clamps(): void
    {
        $product = Product::create($this->site->id, 'Zero', 'zero', 1000, stockQuantity: 2);
        $saved = $this->repository->save($product);

        $this->repository->decrementStock($saved->getId(), 10);

        $found = $this->repository->findById($saved->getId());
        $this->assertEquals(0, $found->getStockQuantity());
    }
}
