<?php

uses(Tests\TestCase::class)->group('growmart');
uses(\Illuminate\Foundation\Testing\WithFaker::class);

use App\Domain\GrowMart\ValueObjects\Money;
use App\Domain\GrowMart\ValueObjects\ProductStatus;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('roles', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('guard_name')->default('web');
        $table->timestamps();
    });
    DB::table('roles')->insert(['name' => 'Client', 'guard_name' => 'web']);
    DB::table('roles')->insert(['name' => 'Member', 'guard_name' => 'web']);
    Schema::create('permissions', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('guard_name')->default('web');
        $table->timestamps();
    });
    Schema::create('model_has_roles', function ($table) {
        $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
        $table->morphs('model');
        $table->primary(['role_id', 'model_id', 'model_type']);
    });
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable()->unique();
        $table->json('account_types')->nullable();
        $table->string('account_type')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('growmart_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->foreignId('parent_id')->nullable()->constrained('growmart_categories');
        $table->text('description')->nullable();
        $table->string('image')->nullable();
        $table->integer('sort_order')->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    Schema::create('growmart_warehouses', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('province');
        $table->string('city');
        $table->string('address');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    Schema::create('growmart_products', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('unit')->nullable();
        $table->integer('price');
        $table->integer('compare_price')->nullable();
        $table->foreignId('category_id')->constrained('growmart_categories');
        $table->string('status')->default('active');
        $table->timestamps();
    });
    Schema::create('growmart_inventory', function ($table) {
        $table->id();
        $table->foreignId('warehouse_id')->constrained('growmart_warehouses');
        $table->foreignId('product_id')->constrained('growmart_products');
        $table->integer('quantity');
        $table->integer('low_stock_threshold')->default(10);
        $table->timestamp('alert_sent_at')->nullable();
        $table->timestamps();
    });
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
    $p1 = GrowMartProduct::factory()->create([
        'name' => 'Test Product',
        'slug' => '',
        'category_id' => $this->category->id,
    ]);
    $p2 = GrowMartProduct::factory()->create([
        'name' => 'Test Product',
        'slug' => '',
        'category_id' => $this->category->id,
    ]);

    expect($p1->slug)->toStartWith('test-product');
    expect($p2->slug)->toStartWith('test-product');
    expect($p1->slug)->not->toBe($p2->slug);
});
