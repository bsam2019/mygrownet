<?php

namespace App\Application\GrowBuilder\UseCases\Product;

use App\Domain\GrowBuilder\Product\DTOs\ProductData;
use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $productId, int $siteId, ProductData $data): ?GrowBuilderProduct
    {
        $product = $this->productRepository->findByIdForSite($productId, $siteId);
        
        if (!$product) {
            return null;
        }

        return $this->productRepository->update($product, $data->toArray());
    }
}
