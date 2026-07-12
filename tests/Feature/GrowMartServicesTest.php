<?php

use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Services\OrderService;
use App\Models\GrowMart\GrowMartCategory;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cartService = app(CartService::class);
    $this->orderService = app(OrderService::class);

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
});

it('adds items to cart', function () {
    $summary = $this->cartService->addItem($this->user->id, $this->product->id, 3);

    expect($summary['item_count'])->toBe(3);
    expect($summary['items'][0]['product_id'])->toBe($this->product->id);
});

it('updates cart item quantity', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 2);
    $summary = $this->cartService->updateQuantity($this->user->id, $this->product->id, 5);

    expect($summary['item_count'])->toBe(5);
});

it('removes items from cart', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 2);
    $summary = $this->cartService->removeItem($this->user->id, $this->product->id);

    expect($summary['item_count'])->toBe(0);
});

it('clears cart', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 2);
    $summary = $this->cartService->clearCart($this->user->id);

    expect($summary['item_count'])->toBe(0);
});

it('creates order from cart', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 3);

    $order = $this->orderService->createOrder($this->user->id, [
        'delivery_method' => 'yango',
        'delivery_zone' => 'Lusaka',
        'delivery_address' => '123 Main St',
        'contact_phone' => '0977123456',
    ]);

    expect($order->order_number)->toStartWith('GM-');
    expect($order->status)->toBe('pending');
    expect($order->total)->toBe(3000 + 3000); // 3 * 1000 subtotal + 3000 delivery
    expect($order->items->count())->toBe(1);
});

it('rejects empty cart on order creation', function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Cart is empty.');

    $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
});

it('rejects order when stock insufficient', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 999);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Insufficient stock');

    $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
});

it('deducts stock after order placement', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 10);
    $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);

    $remaining = $this->product->inventory()->sum('quantity');
    expect((int) $remaining)->toBe(40); // started with 50, deducted 10
});

it('lists orders for user', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 1);
    $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);

    $orders = $this->orderService->getOrdersForUser($this->user->id);
    expect($orders->count())->toBe(1);
});

it('cancels pending order', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 1);
    $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);

    $cancelled = $this->orderService->cancelOrder($order->id, $this->user->id);
    expect($cancelled->status)->toBe('cancelled');
    expect($cancelled->cancelled_at)->not->toBeNull();
});

it('prevents cancelling delivered order', function () {
    $this->cartService->addItem($this->user->id, $this->product->id, 1);
    $order = $this->orderService->createOrder($this->user->id, ['delivery_method' => 'pickup']);
    $order->update(['status' => 'delivered']);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('cannot be cancelled');

    $this->orderService->cancelOrder($order->id, $this->user->id);
});
