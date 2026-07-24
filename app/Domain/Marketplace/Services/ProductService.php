<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Entities\Product;
use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\Repositories\CategoryRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\ProductStatus;
use App\Domain\Marketplace\ValueObjects\Money;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function create(int $sellerId, array $data): array
    {
        $slug = $this->generateUniqueSlug($data['name']);

        $product = new Product(
            id: null,
            sellerId: $sellerId,
            categoryId: $data['category_id'],
            name: $data['name'],
            slug: $slug,
            description: $data['description'],
            price: Money::fromNgwee($data['price']),
            comparePrice: isset($data['compare_price']) ? Money::fromNgwee($data['compare_price']) : null,
            stockQuantity: $data['stock_quantity'] ?? 0,
            images: $data['images'] ?? [],
            status: ProductStatus::pending(),
            isFeatured: false,
            createdAt: new \DateTimeImmutable(),
        );

        $saved = $this->productRepository->save($product);
        return $saved->toArray();
    }

    public function update(int $productId, array $data): array
    {
        $product = $this->productRepository->findById($productId);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        $slug = $product->slug;
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $slug = $this->generateUniqueSlug($data['name'], $productId);
        }

        $updated = new Product(
            id: $product->id,
            sellerId: $data['seller_id'] ?? $product->sellerId,
            categoryId: $data['category_id'] ?? $product->categoryId,
            name: $data['name'] ?? $product->name,
            slug: $slug,
            description: $data['description'] ?? $product->description,
            price: isset($data['price']) ? Money::fromNgwee($data['price']) : $product->price,
            comparePrice: array_key_exists('compare_price', $data)
                ? (isset($data['compare_price']) ? Money::fromNgwee($data['compare_price']) : null)
                : $product->comparePrice,
            stockQuantity: $data['stock_quantity'] ?? $product->stockQuantity,
            images: $data['images'] ?? $product->images,
            status: isset($data['status']) ? ProductStatus::fromString($data['status']) : $product->status,
            isFeatured: $data['is_featured'] ?? $product->isFeatured,
            createdAt: $product->createdAt,
        );

        $saved = $this->productRepository->save($updated);
        return $saved->toArray();
    }

    public function delete(int $productId): void
    {
        $this->productRepository->delete($productId);
    }

    public function getById(int $id): ?array
    {
        $product = $this->productRepository->findById($id);
        return $product ? $product->toArray() : null;
    }

    public function getBySlug(string $slug): ?array
    {
        $product = $this->productRepository->findBySlug($slug);
        return $product ? $product->toArray() : null;
    }

    public function getBySeller(int $sellerId, array $filters = [], int $perPage = 20): array
    {
        $products = $this->productRepository->findBySeller($sellerId, $filters, $perPage);
        return array_map(fn(Product $p) => $p->toArray(), $products);
    }

    public function getActiveProducts(array $filters = [], int $perPage = 20): array
    {
        return $this->productRepository->findActive($filters, $perPage);
    }

    public function getFeaturedProducts(int $limit = 8): array
    {
        $products = $this->productRepository->findFeatured($limit);
        return array_map(fn(Product $p) => $p->toArray(), $products);
    }

    public function approveProduct(int $productId): void
    {
        $this->productRepository->updateStatus($productId, 'active');
    }

    public function rejectProduct(int $productId, string $reason): void
    {
        $this->productRepository->updateStatus($productId, 'rejected', $reason);
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        $this->productRepository->decrementStock($productId, $quantity);
    }

    public function incrementStock(int $productId, int $quantity): void
    {
        $this->productRepository->incrementStock($productId, $quantity);
    }

    public function incrementViews(int $productId): void
    {
        $this->productRepository->incrementViews($productId);
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->getActiveCategories();
    }

    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->productRepository->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
