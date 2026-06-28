<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartCart;
use App\Models\GrowMart\GrowMartProduct;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCart(int $userId): GrowMartCart
    {
        return GrowMartCart::firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(int $userId, int $productId, int $quantity): array
    {
        $product = GrowMartProduct::where('id', $productId)
            ->where('status', 'active')
            ->firstOrFail();

        $cart = $this->getCart($userId);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $this->getSummary($userId);
    }

    public function updateQuantity(int $userId, int $productId, int $quantity): array
    {
        $cart = $this->getCart($userId);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->update(['quantity' => $quantity]);
            }
        }

        return $this->getSummary($userId);
    }

    public function removeItem(int $userId, int $productId): array
    {
        $cart = $this->getCart($userId);
        $cart->items()->where('product_id', $productId)->delete();

        return $this->getSummary($userId);
    }

    public function clearCart(int $userId): array
    {
        $cart = $this->getCart($userId);
        $cart->items()->delete();

        return $this->getSummary($userId);
    }

    public function getSummary(?int $userId): array
    {
        if ($userId === null) {
            return ['item_count' => 0, 'items' => [], 'subtotal' => 0, 'total' => 0];
        }

        $cart = $this->getCart($userId);
        $items = $cart->items()->with('product.category')->get();
        $subtotal = 0;

        $mapped = $items->map(function ($item) use (&$subtotal) {
            $price = $item->product?->price ?? 0;
            $total = $price * $item->quantity;
            $subtotal += $total;
            $stock = (int) ($item->product?->inventory()->sum('quantity') ?? 0);

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product?->name ?? 'Unknown',
                'slug' => $item->product?->slug ?? '',
                'image' => $item->product?->images()->first()?->path ?? null,
                'unit' => $item->product?->unit ?? '',
                'unit_price' => $price,
                'unit_price_formatted' => 'K' . number_format($price / 100, 2),
                'quantity' => $item->quantity,
                'total' => $total,
                'total_formatted' => 'K' . number_format($total / 100, 2),
                'max_stock' => $stock,
            ];
        });

        return [
            'items' => $mapped,
            'item_count' => $mapped->sum('quantity'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => 'K' . number_format($subtotal / 100, 2),
        ];
    }
}
