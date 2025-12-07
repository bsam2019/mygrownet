<?php

namespace Tests\Feature\BizBoost;

class ProductsTest extends BizBoostTestCase
{
    public function test_products_index_page_loads(): void
    {
        $response = $this->actingAsUser()->get('/bizboost/products');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Products/Index')
            ->has('products')
            ->has('categories')
        );
    }

    public function test_products_index_shows_products(): void
    {
        $this->createProduct(['name' => 'Test Product 1', 'price' => 100]);
        $this->createProduct(['name' => 'Test Product 2', 'price' => 200]);

        $response = $this->actingAsUser()->get('/bizboost/products');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Products/Index')
            ->has('products.data', 2)
        );
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $this->createProduct(['name' => 'Dress', 'category' => 'Clothing']);
        $this->createProduct(['name' => 'Shoes', 'category' => 'Footwear']);

        $response = $this->actingAsUser()->get('/bizboost/products?category=Clothing');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.category', 'Clothing')
        );
    }

    public function test_products_can_be_searched(): void
    {
        $this->createProduct(['name' => 'Summer Dress']);
        $this->createProduct(['name' => 'Winter Coat']);

        $response = $this->actingAsUser()->get('/bizboost/products?search=Summer');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Summer Dress')
        );
    }

    public function test_product_create_page_loads(): void
    {
        $response = $this->actingAsUser()->get('/bizboost/products/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Products/Create')
        );
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->actingAsUser()->post('/bizboost/products', [
            'name' => 'New Product',
            'price' => 150,
            'description' => 'A great product',
            'category' => 'Electronics',
            'is_active' => true,
        ]);

        $response->assertRedirect('/bizboost/products');
        $this->assertDatabaseHas('bizboost_products', [
            'business_id' => $this->business->id,
            'name' => 'New Product',
            'price' => 150,
        ]);
    }

    public function test_product_can_be_updated(): void
    {
        $product = $this->createProduct(['name' => 'Old Name', 'price' => 100]);

        $response = $this->actingAsUser()->put("/bizboost/products/{$product->id}", [
            'name' => 'New Name',
            'price' => 200,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_products', [
            'id' => $product->id,
            'name' => 'New Name',
            'price' => 200,
        ]);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = $this->createProduct();

        $response = $this->actingAsUser()->delete("/bizboost/products/{$product->id}");

        $response->assertRedirect('/bizboost/products');
        $this->assertDatabaseMissing('bizboost_products', ['id' => $product->id]);
    }

    public function test_cannot_access_other_business_products(): void
    {
        $otherUser = \App\Models\User::factory()->create();
        $otherBusiness = \App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Business',
            'slug' => 'other-business',
            'industry' => 'salon',
            'onboarding_completed' => true,
        ]);
        $otherProduct = \App\Infrastructure\Persistence\Eloquent\BizBoostProductModel::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Product',
            'price' => 100,
        ]);

        $response = $this->actingAsUser()->get("/bizboost/products/{$otherProduct->id}");

        $response->assertStatus(404);
    }
}
