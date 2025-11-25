<?php

namespace App\Domain\Wedding\Repositories;

use App\Domain\Wedding\Entities\WeddingVendor;
use App\Domain\Wedding\ValueObjects\VendorCategory;

interface WeddingVendorRepositoryInterface
{
    public function save(WeddingVendor $vendor): WeddingVendor;
    
    public function findById(int $id): ?WeddingVendor;
    
    public function findBySlug(string $slug): ?WeddingVendor;
    
    public function findByUserId(int $userId): array;
    
    public function findByCategory(VendorCategory $category): array;
    
    public function findByLocation(string $location): array;
    
    public function findFeaturedVendors(int $limit = 10): array;
    
    public function findVerifiedVendors(): array;
    
    public function searchVendors(array $criteria): array;
    
    public function findTopRatedVendors(int $limit = 10): array;
    
    public function delete(int $id): bool;
    
    public function getVendorStats(): array;
}