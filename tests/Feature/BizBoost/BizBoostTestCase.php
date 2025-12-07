<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class BizBoostTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected BizBoostBusinessModel $business;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@bizboost.test',
            'name' => 'Test User',
        ]);

        // Create a test business
        $this->business = BizBoostBusinessModel::create([
            'user_id' => $this->user->id,
            'name' => 'Test Business',
            'slug' => 'test-business',
            'industry' => 'boutique',
            'description' => 'A test business for BizBoost',
            'phone' => '+260971234567',
            'whatsapp' => '+260971234567',
            'email' => 'test@testbusiness.com',
            'city' => 'Lusaka',
            'country' => 'Zambia',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    protected function actingAsUser(): static
    {
        return $this->actingAs($this->user);
    }

    protected function createProduct(array $attributes = []): \App\Infrastructure\Persistence\Eloquent\BizBoostProductModel
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostProductModel::create(array_merge([
            'business_id' => $this->business->id,
            'name' => 'Test Product',
            'price' => 100,
            'is_active' => true,
        ], $attributes));
    }

    protected function createCustomer(array $attributes = []): \App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostCustomerModel::create(array_merge([
            'business_id' => $this->business->id,
            'name' => 'Test Customer',
            'phone' => '+260971234567',
            'is_active' => true,
        ], $attributes));
    }

    protected function createPost(array $attributes = []): \App\Infrastructure\Persistence\Eloquent\BizBoostPostModel
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostPostModel::create(array_merge([
            'business_id' => $this->business->id,
            'caption' => 'Test post caption',
            'status' => 'draft',
        ], $attributes));
    }

    protected function createSale(array $attributes = []): \App\Infrastructure\Persistence\Eloquent\BizBoostSaleModel
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostSaleModel::create(array_merge([
            'business_id' => $this->business->id,
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
            'currency' => 'ZMW',
            'sale_date' => now(),
            'source' => 'manual',
        ], $attributes));
    }

    protected function createTemplate(array $attributes = []): \App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel
    {
        return \App\Infrastructure\Persistence\Eloquent\BizBoostTemplateModel::create(array_merge([
            'name' => 'Test Template',
            'slug' => 'test-template-' . uniqid(),
            'category' => 'social',
            'template_data' => ['type' => 'test'],
            'is_active' => true,
        ], $attributes));
    }
}
