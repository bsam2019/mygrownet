<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Entities\Product;
use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\ProductStatus;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceProduct;
use Illuminate\Support\Facades\Storage;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        $model = MarketplaceProduct::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Product
    {
        $model = MarketplaceProduct::where('slug', $slug)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySeller(int $sellerId, array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceProduct::where('seller_id', $sellerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function findActive(array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceProduct::where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->whereHas('seller', fn($q) => $q->where('is_active', true));

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
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
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort'] ?? 'newest';
        $query = match ($sortBy) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('views'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function findByCategory(int $categoryId, array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceProduct::where('category_id', $categoryId)->where('status', 'active');

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function findFeatured(int $limit = 8): array
    {
        return MarketplaceProduct::with(['seller', 'category'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->where('stock_quantity', '>', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function search(string $query, array $filters = [], int $perPage = 20): array
    {
        $q = MarketplaceProduct::where('status', 'active')
            ->where(function ($qb) use ($query) {
                $qb->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            });

        if (!empty($filters['category_id'])) {
            $q->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['min_price'])) {
            $q->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $q->where('price', '<=', $filters['max_price']);
        }

        return $q->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function save(Product $product): Product
    {
        $data = [
            'seller_id' => $product->sellerId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => $product->price->amount(),
            'compare_price' => $product->comparePrice?->amount(),
            'stock_quantity' => $product->stockQuantity,
            'images' => $product->images,
            'status' => $product->status->value(),
            'is_featured' => $product->isFeatured,
        ];

        if ($product->id) {
            MarketplaceProduct::where('id', $product->id)->update($data);
            return $this->findById($product->id);
        }

        $model = MarketplaceProduct::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $productId, string $status, ?string $rejectionReason = null): void
    {
        $data = ['status' => $status];
        if ($rejectionReason !== null) {
            $data['rejection_reason'] = $rejectionReason;
        }
        MarketplaceProduct::where('id', $productId)->update($data);
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

    public function delete(int $productId): void
    {
        $model = MarketplaceProduct::find($productId);
        if ($model) {
            foreach ($model->images ?? [] as $image) {
                Storage::disk('public')->delete($image);
            }
            $model->delete();
        }
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = MarketplaceProduct::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    private function toDomainEntity(MarketplaceProduct $model): Product
    {
        return new Product(
            id: $model->id,
            sellerId: $model->seller_id,
            categoryId: $model->category_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            price: Money::fromNgwee($model->price),
            comparePrice: $model->compare_price ? Money::fromNgwee($model->compare_price) : null,
            stockQuantity: $model->stock_quantity,
            images: $model->images ?? [],
            status: ProductStatus::fromString($model->status),
            isFeatured: $model->is_featured ?? false,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
        );
    }
}
