<?php

namespace App\Domain\Marketplace\Repositories;

use App\Domain\Marketplace\Entities\Seller;

interface SellerRepositoryInterface
{
    public function findById(int $id): ?Seller;
    public function findByUserId(int $userId): ?Seller;
    public function findActive(array $filters = [], int $perPage = 20): array;
    public function save(Seller $seller): Seller;
    public function updateTrustLevel(int $sellerId, string $trustLevel): void;
    public function updateKycStatus(int $sellerId, string $status): void;
    public function incrementOrderCount(int $sellerId): void;
    public function updateRating(int $sellerId, float $rating): void;
}
