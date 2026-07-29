<?php

namespace Tests\Feature\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Product as ProductEntity;
use App\Domain\GrowBuilder\ValueObjects\Money as MoneyVO;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPage;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowBuilderSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->site = GrowBuilderSite::create([
            'user_id' => $this->user->id,
            'name' => 'Shop',
            'subdomain' => 'shop-' . uniqid(),
            'plan' => 'free',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_product_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/products");
        $response->assertStatus(200);
    }

    public function test_product_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/products/create");
        $response->assertStatus(200);
    }

    public function test_product_store(): void
    {
        $response = $this->actingAs($this->user)->post("/growbuilder/sites/{$this->site->id}/products", [
            'name' => 'Test Product',
            'price' => 29.99,
            'stock_quantity' => 10,
            'category' => 'general',
        ]);

        $this->assertContains($response->status(), [302, 422]);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->get("/growbuilder/sites/{$this->site->id}/products");
        $response->assertRedirect('/login');
    }

    public function test_order_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get("/growbuilder/sites/{$this->site->id}/orders");
        $response->assertStatus(200);
    }
}
