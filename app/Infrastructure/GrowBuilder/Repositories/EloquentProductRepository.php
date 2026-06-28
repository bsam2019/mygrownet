<?php

namespace App\Infrastructure\GrowBuilder\Repositories;

use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?GrowBuilderProduct
    {
        return GrowBuilderProduct::find($id);
    }

    public function findByIdForSite(int $id, int $siteId): ?GrowBuilderProduct
    {
        return GrowBuilderProduct::where('site_id', $siteId)
            ->where('id', $id)
            ->first();
    }

    public function getAllForSite(int $siteId, int $perPage = 20): LengthAwarePaginator
    {
        return GrowBuilderProduct::where('site_id', $siteId)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getActiveForSite(int $siteId): Collection
    {
        return GrowBuilderProduct::where('site_id', $siteId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    public function getCategoriesForSite(int $siteId): Collection
    {
        return GrowBuilderProduct::where('site_id', $siteId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    public function countForSite(int $siteId): int
    {
        return GrowBuilderProduct::where('site_id', $siteId)->count();
    }

    public function create(int $siteId, array $data): GrowBuilderProduct
    {
        $slug = $this->generateUniqueSlug($siteId, $data['name']);

        return GrowBuilderProduct::create([
            'site_id' => $siteId,
            'slug' => $slug,
            ...$data,
        ]);
    }

    public function update(GrowBuilderProduct $product, array $data): GrowBuilderProduct
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(GrowBuilderProduct $product): bool
    {
        return $product->delete();
    }

    public function generateUniqueSlug(int $siteId, string $name): string
    {
        $slug = Str::slug($name);
        $existingCount = GrowBuilderProduct::where('site_id', $siteId)
            ->where('slug', 'like', $slug . '%')
            ->count();

        if ($existingCount > 0) {
            $slug .= '-' . ($existingCount + 1);
        }

        return $slug;
    }
}
