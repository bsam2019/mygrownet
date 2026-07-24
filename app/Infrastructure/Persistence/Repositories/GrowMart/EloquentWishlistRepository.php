<?php

namespace App\Infrastructure\Persistence\Repositories\GrowMart;

use App\Domain\GrowMart\Repositories\WishlistRepositoryInterface;
use App\Models\GrowMart\GrowMartWishlistItem;

class EloquentWishlistRepository implements WishlistRepositoryInterface
{
    public function findByUser(int $userId, int $perPage = 20): array
    {
        return GrowMartWishlistItem::with('product.images', 'product.category')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage)
            ->toArray();
    }

    public function findProductIdsByUser(int $userId): array
    {
        return GrowMartWishlistItem::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        return GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    public function findByUserAndProduct(int $userId, int $productId): ?array
    {
        $model = GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        return $model?->toArray();
    }

    public function add(int $userId, int $productId): array
    {
        $model = GrowMartWishlistItem::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
        return $model->toArray();
    }

    public function remove(int $userId, int $productId): void
    {
        GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function exists(int $userId, int $productId): bool
    {
        return GrowMartWishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
