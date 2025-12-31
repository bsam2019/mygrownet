<?php

namespace App\Application\GrowBuilder\UseCases\Product;

use App\Domain\GrowBuilder\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $siteId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->productRepository->getAllForSite($siteId, $perPage);
    }
}
