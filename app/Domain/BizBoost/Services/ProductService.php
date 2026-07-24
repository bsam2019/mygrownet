<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Product;
use App\Domain\BizBoost\Repositories\ProductRepositoryInterface;
use App\Domain\BizBoost\Repositories\CategoryRepositoryInterface;
use App\Domain\BizBoost\Repositories\BusinessRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private CategoryRepositoryInterface $categoryRepo,
        private BusinessRepositoryInterface $businessRepo,
    ) {}

    public function getProducts(int $businessId, array $filters = []): array
    {
        return $this->productRepo->findByBusiness($businessId, $filters);
    }

    public function getActiveProducts(int $businessId, array $filters = []): array
    {
        return $this->productRepo->findActiveByBusiness($businessId, $filters);
    }

    public function findProduct(int $id): ?Product
    {
        return $this->productRepo->findById($id);
    }

    public function createProduct(array $data): Product
    {
        return $this->productRepo->save(Product::create($data));
    }

    public function updateProduct(int $id, array $data): ?Product
    {
        $existing = $this->productRepo->findById($id);
        if (!$existing) {
            return null;
        }
        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->productRepo->save(Product::reconstitute($merged));
    }

    public function deleteProduct(int $id): void
    {
        $this->productRepo->delete($id);
    }

    public function countByBusiness(int $businessId): int
    {
        return $this->productRepo->countByBusiness($businessId);
    }

    public function storeProductImages(int $productId, array $images, int $businessId): void
    {
        foreach ($images as $index => $image) {
            $path = $image->store("bizboost/products/{$businessId}", 'public');
            \App\Infrastructure\Persistence\Eloquent\BizBoostProductImageModel::create([
                'product_id' => $productId,
                'path' => $path,
                'filename' => $image->getClientOriginalName(),
                'file_size' => $image->getSize(),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    public function deleteProductImage(int $productId, int $imageId): void
    {
        $image = \App\Infrastructure\Persistence\Eloquent\BizBoostProductImageModel::where('product_id', $productId)
            ->findOrFail($imageId);
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }
}