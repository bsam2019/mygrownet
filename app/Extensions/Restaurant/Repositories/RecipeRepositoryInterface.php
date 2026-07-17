<?php

declare(strict_types=1);

namespace App\Extensions\Restaurant\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Extensions\Restaurant\Entities\Recipe;
use App\Extensions\Restaurant\ValueObjects\RecipeId;

interface RecipeRepositoryInterface
{
    public function findById(RecipeId $id): ?Recipe;
    public function findByCompany(CompanyId $companyId): array;
    public function save(Recipe $recipe): Recipe;
    public function delete(RecipeId $id): void;
}
