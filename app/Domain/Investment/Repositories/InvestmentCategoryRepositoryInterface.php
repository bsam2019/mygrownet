<?php

declare(strict_types=1);

namespace App\Domain\Investment\Repositories;

use App\Domain\Investment\Entities\InvestmentCategory;

interface InvestmentCategoryRepositoryInterface
{
    public function findById(int $id): ?InvestmentCategory;

    public function save(InvestmentCategory $category): InvestmentCategory;

    public function findActive(): array;

    public function findBySlug(string $slug): ?InvestmentCategory;
}
