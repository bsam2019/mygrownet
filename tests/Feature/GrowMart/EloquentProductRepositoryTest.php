<?php

namespace Tests\Feature\GrowMart;

use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GrowMartCategory $category;
    private GrowMartWarehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = GrowMartCategory::factory()->create();
        $this->warehouse = GrowMartWarehouse::factory()->create();
    }

    #[Test]
    public function creates_product(): void
    {
        $product = GrowMartProduct::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'category_id' => $this->category->id,
            'price' => 1000,
            'status' => 'active',
            'unit' => 'pcs',
        ]);

        $this->assertDatabaseHas('growmart_products', ['id' => $product->id, 'name' => 'Test Product']);
    }

    #[Test]
    public function finds_product_by_id(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        $found = GrowMartProduct::with(['category', 'images', 'inventory'])->find($product->id);
        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
    }

    #[Test]
    public function finds_product_by_slug(): void
    {
        $product = GrowMartProduct::factory()->create([
            'category_id' => $this->category->id,
            'slug' => 'unique-slug-' . uniqid(),
        ]);
        $found = GrowMartProduct::where('slug', $product->slug)->first();
        $this->assertNotNull($found);
    }

    #[Test]
    public function updates_product(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id, 'price' => 1000]);
        $product->update(['price' => 2000]);
        $this->assertEquals(2000, $product->fresh()->price);
    }

    #[Test]
    public function deletes_product(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        $product->delete();
        $this->assertModelMissing($product);
    }

    #[Test]
    public function total_stock_accessor_sums_inventory(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        $warehouse2 = GrowMartWarehouse::factory()->create();
        GrowMartInventory::factory()->create(['warehouse_id' => $this->warehouse->id, 'product_id' => $product->id, 'quantity' => 30]);
        GrowMartInventory::factory()->create(['warehouse_id' => $warehouse2->id, 'product_id' => $product->id, 'quantity' => 20]);
        $this->assertEquals(50, $product->total_stock);
    }

    #[Test]
    public function formatted_price_formats_correctly(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id, 'price' => 1550]);
        $this->assertEquals('K15.50', $product->formatted_price);
    }

    #[Test]
    public function find_active_filters_by_status(): void
    {
        GrowMartProduct::factory()->create(['category_id' => $this->category->id, 'status' => 'active']);
        GrowMartProduct::factory()->create(['category_id' => $this->category->id, 'status' => 'discontinued']);
        $active = GrowMartProduct::where('status', 'active')->get();
        $this->assertEquals(1, $active->count());
    }

    #[Test]
    public function inventory_low_stock_detection(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        $inv = GrowMartInventory::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'low_stock_threshold' => 10,
        ]);
        $this->assertTrue($inv->isLowStock());
        $this->assertFalse($inv->isOutOfStock());
    }

    #[Test]
    public function inventory_out_of_stock_detection(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        $inv = GrowMartInventory::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $product->id,
            'quantity' => 0,
        ]);
        $this->assertTrue($inv->isOutOfStock());
    }

    #[Test]
    public function category_hierarchy(): void
    {
        $parent = GrowMartCategory::factory()->create(['parent_id' => null]);
        $child = GrowMartCategory::factory()->create(['parent_id' => $parent->id]);
        $this->assertEquals(1, $parent->children->count());
        $this->assertEquals($parent->id, $child->parent->id);
    }

    #[Test]
    public function slug_generated_on_create(): void
    {
        $cat = GrowMartCategory::factory()->create(['name' => 'Fresh Veggies', 'slug' => '']);
        $this->assertEquals('fresh-veggies', $cat->slug);
    }

    #[Test]
    public function product_slug_unique_on_create(): void
    {
        $p1 = GrowMartProduct::factory()->create(['name' => 'Same Name', 'slug' => '', 'category_id' => $this->category->id]);
        $p2 = GrowMartProduct::factory()->create(['name' => 'Same Name', 'slug' => '', 'category_id' => $this->category->id]);
        $this->assertStringStartsWith('same-name', $p1->slug);
        $this->assertStringStartsWith('same-name', $p2->slug);
        $this->assertNotEquals($p1->slug, $p2->slug);
    }

    #[Test]
    public function warehouse_has_inventory(): void
    {
        $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);
        GrowMartInventory::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);
        $this->assertEquals(1, $this->warehouse->inventory->count());
        $this->assertEquals(100, $this->warehouse->inventory->first()->quantity);
    }
}
