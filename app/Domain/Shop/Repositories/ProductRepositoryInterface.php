<?php

namespace App\Domain\Shop\Repositories;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function getActive(int $perPage = 12): LengthAwarePaginator;
    public function getFeatured(int $limit = 4): array;
    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator;
}
