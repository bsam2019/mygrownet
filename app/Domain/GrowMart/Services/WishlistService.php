<?php

namespace App\Domain\GrowMart\Services;

use App\Domain\GrowMart\Repositories\WishlistRepositoryInterface;

class WishlistService
{
    public function __construct(
        private readonly WishlistRepositoryInterface $wishlistRepository,
    ) {}

    public function getWishlist(int $userId, int $perPage = 20): array
    {
        return $this->wishlistRepository->findByUser($userId, $perPage);
    }

    public function isWishlisted(int $userId, int $productId): bool
    {
        return $this->wishlistRepository->isWishlisted($userId, $productId);
    }

    public function getWishlistProductIds(int $userId): array
    {
        return $this->wishlistRepository->findProductIdsByUser($userId);
    }

    public function toggle(int $userId, int $productId): array
    {
        $existing = $this->wishlistRepository->findByUserAndProduct($userId, $productId);

        if ($existing) {
            $this->wishlistRepository->remove($userId, $productId);
            return ['wishlisted' => false, 'message' => 'Removed from wishlist'];
        }

        $this->wishlistRepository->add($userId, $productId);
        return ['wishlisted' => true, 'message' => 'Added to wishlist'];
    }

    public function remove(int $userId, int $productId): void
    {
        $this->wishlistRepository->remove($userId, $productId);
    }
}
