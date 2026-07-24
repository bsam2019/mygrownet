<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Product;
use App\Domain\GrowBuilder\Repositories\ProductRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\ProductId;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product
    {
        $model = GrowBuilderProduct::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByIdForSite(ProductId $id, SiteId $siteId): ?Product
    {
        $model = GrowBuilderProduct::where('site_id', $siteId->value())
            ->where('id', $id->value())
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlugForSite(SiteId $siteId, string $slug): ?Product
    {
        $model = GrowBuilderProduct::where('site_id', $siteId->value())
            ->where('slug', $slug)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function getAllForSitePaginated(SiteId $siteId, int $perPage = 20): LengthAwarePaginator
    {
        $paginator = GrowBuilderProduct::where('site_id', $siteId->value())
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $paginator->getCollection()->transform(fn($model) => $this->toDomainEntity($model));

        return $paginator;
    }

    public function getAllForSite(SiteId $siteId): array
    {
        return GrowBuilderProduct::where('site_id', $siteId->value())
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function getActiveForSite(SiteId $siteId): array
    {
        return GrowBuilderProduct::where('site_id', $siteId->value())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function getCategoriesForSite(SiteId $siteId): Collection
    {
        return GrowBuilderProduct::where('site_id', $siteId->value())
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    public function countForSite(SiteId $siteId): int
    {
        return GrowBuilderProduct::where('site_id', $siteId->value())->count();
    }

    public function save(Product $product): Product
    {
        $data = [
            'site_id' => $product->getSiteId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $product->getDescription(),
            'short_description' => $product->getShortDescription(),
            'price' => $product->getPrice()->getAmountInNgwee(),
            'compare_price' => $product->getComparePrice()?->getAmountInNgwee(),
            'images' => $product->getImages(),
            'stock_quantity' => $product->getStockQuantity(),
            'track_stock' => $product->isTrackingStock(),
            'sku' => $product->getSku(),
            'category' => $product->getCategory(),
            'variants' => $product->getVariants(),
            'attributes' => $product->getAttributes(),
            'weight' => $product->getWeight(),
            'is_active' => $product->isActive(),
            'is_featured' => $product->isFeatured(),
            'sort_order' => $product->getSortOrder(),
        ];

        if ($product->getId()) {
            $model = GrowBuilderProduct::findOrFail($product->getId()->value());
            $model->update($data);
        } else {
            $data['slug'] = $this->generateUniqueSlug($product->getSiteId(), $product->getName());
            $model = GrowBuilderProduct::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(ProductId $id): void
    {
        GrowBuilderProduct::destroy($id->value());
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

    public function hasStock(ProductId $id, int $quantity): bool
    {
        $model = GrowBuilderProduct::find($id->value());
        if (!$model) {
            return false;
        }
        if (!$model->track_stock) {
            return true;
        }
        return $model->stock_quantity >= $quantity;
    }

    public function decrementStock(ProductId $id, int $quantity): void
    {
        $model = GrowBuilderProduct::find($id->value());
        if ($model && $model->track_stock) {
            $model->decrement('stock_quantity', $quantity);
        }
    }

    private function toDomainEntity(GrowBuilderProduct $model): Product
    {
        return Product::reconstitute(
            id: ProductId::fromInt($model->id),
            siteId: $model->site_id,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            shortDescription: $model->short_description,
            price: Money::fromNgwee($model->price),
            comparePrice: $model->compare_price ? Money::fromNgwee($model->compare_price) : null,
            images: $model->images ?? [],
            stockQuantity: $model->stock_quantity ?? 0,
            trackStock: $model->track_stock ?? true,
            sku: $model->sku,
            category: $model->category,
            variants: $model->variants ?? [],
            attributes: $model->attributes ?? [],
            weight: $model->weight,
            isActive: $model->is_active ?? true,
            isFeatured: $model->is_featured ?? false,
            sortOrder: $model->sort_order ?? 0,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
