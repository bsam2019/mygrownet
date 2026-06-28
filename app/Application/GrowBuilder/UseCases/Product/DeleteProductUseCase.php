<?php

namespace App\Application\GrowBuilder\UseCases\Product;

use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;

class DeleteProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $productId, int $siteId): bool
    {
        $product = $this->productRepository->findByIdForSite($productId, $siteId);
        
        if (!$product) {
            return false;
        }

        return $this->productRepository->delete($product);
    }
}
