<?php

namespace App\Domain\GrowBuilder\Product\Repositories;

use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?GrowBuilderProduct;
    
    public function findByIdForSite(int $id, int $siteId): ?GrowBuilderProduct;
    
    public function getAllForSite(int $siteId, int $perPage = 20): LengthAwarePaginator;
    
    public function getActiveForSite(int $siteId): Collection;
    
    public function getCategoriesForSite(int $siteId): Collection;
    
    public function countForSite(int $siteId): int;
    
    public function create(int $siteId, array $data): GrowBuilderProduct;
    
    public function update(GrowBuilderProduct $product, array $data): GrowBuilderProduct;
    
    public function delete(GrowBuilderProduct $product): bool;
    
    public function generateUniqueSlug(int $siteId, string $name): string;
}
