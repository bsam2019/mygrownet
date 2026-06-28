<?php

namespace App\Domain\Marketplace\Services;

use App\Models\MarketplaceProduct;
use App\Models\MarketplaceCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function create(int $sellerId, array $data): MarketplaceProduct
    {
        $slug = $this->generateUniqueSlug($data['name']);
        
        return MarketplaceProduct::create([
            'seller_id' => $sellerId,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'price' => $data['price'],
            'compare_price' => $data['compare_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'images' => $data['images'] ?? [],
            'status' => 'pending',
            'is_featured' => false,
        ]);
    }

    public function update(int $productId, array $data): MarketplaceProduct
    {
        $product = MarketplaceProduct::findOrFail($productId);
        
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $productId);
        }

        $product->update($data);
        return $product->fresh();
    }

    public function delete(int $productId): void
    {
        $product = MarketplaceProduct::findOrFail($productId);
        
        // Delete images from storage
        foreach ($product->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }
        
        $product->delete();
    }

    public function getById(int $id): ?MarketplaceProduct
    {
        return MarketplaceProduct::with(['seller.user', 'category'])->find($id);
    }

    public function getBySlug(string $slug): ?MarketplaceProduct
    {
        return MarketplaceProduct::with(['seller.user', 'category'])
            ->where('slug', $slug)
            ->first();
    }

    public function getBySeller(int $sellerId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MarketplaceProduct::with('category')
            ->where('seller_id', $sellerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getActiveProducts(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MarketplaceProduct::with(['seller', 'category'])
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->whereHas('seller', fn($q) => $q->where('is_active', true));

        // Support both single category_id and array of category_ids
        if (!empty($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
        } elseif (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (!empty($filters['province'])) {
            $query->whereHas('seller', fn($q) => $q->where('province', $filters['province']));
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        $sortBy = $filters['sort'] ?? 'newest';
        $query = match ($sortBy) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate($perPage);
    }

    public function getFeaturedProducts(int $limit = 8): \Illuminate\Support\Collection
    {
        return MarketplaceProduct::with(['seller', 'category'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->where('stock_quantity', '>', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function approveProduct(int $productId): void
    {
        MarketplaceProduct::where('id', $productId)->update(['status' => 'active']);
    }

    public function rejectProduct(int $productId, string $reason): void
    {
        MarketplaceProduct::where('id', $productId)->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        MarketplaceProduct::where('id', $productId)
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity);
    }

    public function incrementStock(int $productId, int $quantity): void
    {
        MarketplaceProduct::where('id', $productId)->increment('stock_quantity', $quantity);
    }

    public function incrementViews(int $productId): void
    {
        MarketplaceProduct::where('id', $productId)->increment('views');
    }

    public function getCategories(): \Illuminate\Support\Collection
    {
        return MarketplaceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (MarketplaceProduct::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
