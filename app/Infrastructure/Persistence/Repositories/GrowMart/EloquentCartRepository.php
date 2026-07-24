<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\CartRepositoryInterface;
use App\Models\GrowMart\GrowMartCart;
use App\Models\GrowMart\GrowMartCartItem;

class EloquentCartRepository implements CartRepositoryInterface
{
    public function findByUser(int $userId): ?array
    {
        $model = GrowMartCart::where('user_id', $userId)->first();
        return $model?->toArray();
    }

    public function createForUser(int $userId): array
    {
        $model = GrowMartCart::create(['user_id' => $userId]);
        return $model->toArray();
    }

    public function firstOrCreate(int $userId): array
    {
        $model = GrowMartCart::firstOrCreate(['user_id' => $userId]);
        return $model->toArray();
    }

    public function findItem(int $cartId, int $productId): ?array
    {
        $item = GrowMartCartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
        return $item?->toArray();
    }

    public function addItem(int $cartId, array $data): array
    {
        $item = GrowMartCartItem::create(array_merge(['cart_id' => $cartId], $data));
        return $item->fresh()->toArray();
    }

    public function updateItemQuantity(int $itemId, int $quantity): void
    {
        GrowMartCartItem::where('id', $itemId)->update(['quantity' => $quantity]);
    }

    public function incrementItemQuantity(int $itemId, int $amount): void
    {
        GrowMartCartItem::where('id', $itemId)->increment('quantity', $amount);
    }

    public function deleteItem(int $itemId): void
    {
        GrowMartCartItem::where('id', $itemId)->delete();
    }

    public function deleteItemByProduct(int $cartId, int $productId): void
    {
        GrowMartCartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function clearItems(int $cartId): void
    {
        GrowMartCartItem::where('cart_id', $cartId)->delete();
    }

    public function getItems(int $cartId): array
    {
        $items = GrowMartCartItem::with('product.category')
            ->where('cart_id', $cartId)
            ->get();
        return $items->toArray();
    }
}
