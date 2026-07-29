<?php

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\Repositories\CategoryRepositoryInterface;
use App\Models\Marketplace\MarketplaceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(CategoryRepositoryInterface::class);
    }

    #[Test]
    public function get_active_categories_returns_only_active(): void
    {
        MarketplaceCategory::create(['name' => 'Active Cat', 'slug' => 'active-cat', 'is_active' => true, 'sort_order' => 1]);
        MarketplaceCategory::create(['name' => 'Inactive Cat', 'slug' => 'inactive-cat', 'is_active' => false, 'sort_order' => 2]);

        $categories = $this->repo->getActiveCategories();

        $this->assertCount(1, $categories);
        $this->assertEquals('Active Cat', $categories[0]['name']);
    }

    #[Test]
    public function find_by_id_returns_category(): void
    {
        $cat = MarketplaceCategory::create(['name' => 'Electronics', 'slug' => 'electronics']);

        $result = $this->repo->findById($cat->id);

        $this->assertNotNull($result);
        $this->assertEquals('Electronics', $result['name']);
    }

    #[Test]
    public function find_by_id_returns_null_for_missing(): void
    {
        $this->assertNull($this->repo->findById(999));
    }

    #[Test]
    public function find_by_slug_returns_category(): void
    {
        MarketplaceCategory::create(['slug' => 'electronics', 'name' => 'Electronics']);

        $result = $this->repo->findBySlug('electronics');

        $this->assertNotNull($result);
        $this->assertEquals('Electronics', $result['name']);
    }

    #[Test]
    public function get_parent_categories_includes_children(): void
    {
        $parent = MarketplaceCategory::create(['name' => 'Parent', 'slug' => 'parent', 'parent_id' => null, 'is_active' => true]);
        MarketplaceCategory::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id, 'is_active' => true]);

        $parents = $this->repo->getParentCategories();

        $this->assertCount(1, $parents);
        $this->assertCount(1, $parents[0]['children']);
        $this->assertEquals('Child', $parents[0]['children'][0]['name']);
    }
}
