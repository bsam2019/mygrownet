<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartWishlistItem;
use App\Models\GrowMart\GrowMartProduct;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    public function getWishlist(int $userId, int $perPage = 20)
    {
        return GrowMartWishlistItem::with('product.images', 'product.category')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        return GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    public function getWishlistProductIds(int $userId): array
    {
        return GrowMartWishlistItem::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
    }

    public function toggle(int $userId, int $productId): array
    {
        $existing = GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['wishlisted' => false, 'message' => 'Removed from wishlist'];
        }

        GrowMartWishlistItem::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['wishlisted' => true, 'message' => 'Added to wishlist'];
    }

    public function remove(int $userId, int $productId): void
    {
        GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }
}
