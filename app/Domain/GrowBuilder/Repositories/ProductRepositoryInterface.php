<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Entities\Product;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product;

    public function findByIdForSite(ProductId $id, SiteId $siteId): ?Product;

    public function findBySlugForSite(SiteId $siteId, string $slug): ?Product;

    public function getAllForSitePaginated(SiteId $siteId, int $perPage = 20): LengthAwarePaginator;

    public function getAllForSite(SiteId $siteId): array;

    public function getActiveForSite(SiteId $siteId): array;

    public function getCategoriesForSite(SiteId $siteId): Collection;

    public function countForSite(SiteId $siteId): int;

    public function save(Product $product): Product;

    public function delete(ProductId $id): void;

    public function generateUniqueSlug(int $siteId, string $name): string;

    public function hasStock(ProductId $id, int $quantity): bool;

    public function decrementStock(ProductId $id, int $quantity): void;
}
