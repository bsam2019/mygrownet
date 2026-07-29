<?php

namespace Tests\Feature\GrowMart;

use App\Domain\GrowMart\Services\CartService;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GrowMartProduct $product;
    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $category = GrowMartCategory::factory()->create();
        $warehouse = GrowMartWarehouse::factory()->create();
        $this->product = GrowMartProduct::factory()->create([
            'category_id' => $category->id,
            'price' => 1000,
            'status' => 'active',
        ]);
        GrowMartInventory::factory()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);
        $this->cartService = app(CartService::class);
    }

    #[Test]
    public function add_item_returns_summary(): void
    {
        $summary = $this->cartService->addItem($this->user->id, $this->product->id, 3);
        $this->assertEquals(3, $summary['item_count']);
        $this->assertEquals($this->product->id, $summary['items'][0]['product_id']);
    }

    #[Test]
    public function add_item_increases_existing_quantity(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 2);
        $summary = $this->cartService->addItem($this->user->id, $this->product->id, 3);
        $this->assertEquals(5, $summary['item_count']);
    }

    #[Test]
    public function add_inactive_product_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $inactive = GrowMartProduct::factory()->create([
            'category_id' => GrowMartCategory::factory()->create()->id,
            'price' => 500,
            'status' => 'discontinued',
        ]);
        $this->cartService->addItem($this->user->id, $inactive->id, 1);
    }

    #[Test]
    public function update_quantity_modifies_item(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 2);
        $summary = $this->cartService->updateQuantity($this->user->id, $this->product->id, 5);
        $this->assertEquals(5, $summary['item_count']);
    }

    #[Test]
    public function update_quantity_to_zero_removes_item(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 2);
        $summary = $this->cartService->updateQuantity($this->user->id, $this->product->id, 0);
        $this->assertEquals(0, $summary['item_count']);
    }

    #[Test]
    public function remove_item_deletes_from_cart(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 2);
        $summary = $this->cartService->removeItem($this->user->id, $this->product->id);
        $this->assertEquals(0, $summary['item_count']);
    }

    #[Test]
    public function clear_cart_empties_all_items(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 2);
        $summary = $this->cartService->clearCart($this->user->id);
        $this->assertEquals(0, $summary['item_count']);
    }

    #[Test]
    public function get_summary_returns_correct_subtotal(): void
    {
        $this->cartService->addItem($this->user->id, $this->product->id, 3);
        $summary = $this->cartService->getSummary($this->user->id);
        $this->assertEquals(3000, $summary['subtotal']);
        $this->assertEquals('K30.00', $summary['subtotal_formatted']);
    }

    #[Test]
    public function get_summary_returns_empty_for_null_user(): void
    {
        $summary = $this->cartService->getSummary(null);
        $this->assertEquals(0, $summary['item_count']);
        $this->assertEquals([], $summary['items']);
    }
}
