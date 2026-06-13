<?php

use App\Domain\GrowMart\ValueObjects\Money;
use App\Domain\GrowMart\ValueObjects\ProductStatus;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = GrowMartCategory::factory()->create();
    $this->warehouse = GrowMartWarehouse::factory()->create(['province' => 'Lusaka', 'city' => 'Lusaka']);
});

it('creates a product with correct price conversion', function () {
    $product = GrowMartProduct::factory()->create([
        'category_id' => $this->category->id,
        'price' => 1500, // K15.00 in ngwee
        'status' => 'active',
    ]);

    expect($product->price)->toBe(1500);
    expect($product->formatted_price)->toBe('K15.00');
});

it('tracks inventory correctly', function () {
    $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);

    GrowMartInventory::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    expect($product->total_stock)->toBe(50);
});

it('detects low stock', function () {
    $product = GrowMartProduct::factory()->create(['category_id' => $this->category->id]);

    GrowMartInventory::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'low_stock_threshold' => 10,
    ]);

    $inventory = $product->inventory->first();
    expect($inventory->isLowStock())->toBeTrue();
    expect($inventory->isOutOfStock())->toBeFalse();
});

it('creates category hierarchy', function () {
    $parent = GrowMartCategory::factory()->create(['parent_id' => null]);
    $child = GrowMartCategory::factory()->create(['parent_id' => $parent->id]);

    expect($parent->children->count())->toBe(1);
    expect($child->parent->id)->toBe($parent->id);
});

it('generates slug on category creation', function () {
    $category = GrowMartCategory::factory()->create(['name' => 'Fresh Vegetables', 'slug' => '']);

    expect($category->slug)->toBe('fresh-vegetables');
});

it('generates unique product slug', function () {
    $p1 = GrowMartProduct::factory()->create(['name' => 'Test Product', 'category_id' => $this->category->id]);
    $p2 = GrowMartProduct::factory()->create(['name' => 'Test Product', 'category_id' => $this->category->id]);

    expect($p1->slug)->toStartWith('test-product');
    expect($p2->slug)->toStartWith('test-product');
    expect($p1->slug)->not->toBe($p2->slug);
});
