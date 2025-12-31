<?php

namespace App\Application\GrowBuilder\UseCases\Product;

use App\Domain\GrowBuilder\Product\DTOs\ProductData;
use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderProduct;

class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $siteId, ProductData $data): GrowBuilderProduct
    {
        return $this->productRepository->create($siteId, $data->toArray());
    }
}
