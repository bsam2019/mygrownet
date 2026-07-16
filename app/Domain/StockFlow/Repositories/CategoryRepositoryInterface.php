<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Category;
use App\Domain\StockFlow\ValueObjects\CategoryId;
use App\Domain\StockFlow\ValueObjects\CompanyId;

interface CategoryRepositoryInterface
{
    public function findById(CategoryId $id): ?Category;
    public function findByCompanyId(CompanyId $companyId): array;
    public function findBySlug(CompanyId $companyId, string $slug): ?Category;
    public function findRoots(CompanyId $companyId): array;
    public function findChildren(CategoryId $parentId): array;
    public function save(Category $category): Category;
    public function delete(CategoryId $id): void;
}
