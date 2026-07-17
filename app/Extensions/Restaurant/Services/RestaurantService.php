<?php

declare(strict_types=1);

namespace App\Extensions\Restaurant\Services;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Restaurant\Entities\Recipe;
use App\Extensions\Restaurant\Entities\RecipeIngredient;
use App\Extensions\Restaurant\Entities\WastageRecord;
use App\Extensions\Restaurant\Repositories\RecipeRepositoryInterface;
use App\Extensions\Restaurant\Repositories\WastageRepositoryInterface;

class RestaurantService
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private WastageRepositoryInterface $wastageRepo,
    ) {}

    public function createRecipe(int $companyId, int $itemId, string $name, float $yieldQuantity, array $ingredients = []): array
    {
        $ingredients = array_map(fn($i) => new RecipeIngredient(
            itemId: (int) $i['sa_item_id'], quantity: (float) $i['quantity'],
            uom: $i['uom'] ?? 'each', wasteFactor: (float) ($i['waste_factor'] ?? 0),
            isSubstitutable: (bool) ($i['is_substitutable'] ?? false),
            sortOrder: (int) ($i['sort_order'] ?? 0),
        ), $ingredients);

        $recipe = Recipe::create(CompanyId::fromInt($companyId), ItemId::fromInt($itemId), $name, $yieldQuantity, $ingredients);
        return $this->recipeRepo->save($recipe)->toArray();
    }

    public function getRecipes(int $companyId): array
    {
        return array_map(fn($r) => $r->toArray(), $this->recipeRepo->findByCompany(CompanyId::fromInt($companyId)));
    }

    public function recordWastage(int $companyId, int $itemId, float $quantity, string $reason, float $unitCost = 0, ?string $notes = null, ?string $occurredAt = null): array
    {
        $record = WastageRecord::create(CompanyId::fromInt($companyId), ItemId::fromInt($itemId), $quantity, $reason, $unitCost, null, null, $notes, $occurredAt);
        return $this->wastageRepo->save($record)->toArray();
    }

    public function getWastageRecords(int $companyId): array
    {
        return array_map(fn($w) => $w->toArray(), $this->wastageRepo->findByCompany(CompanyId::fromInt($companyId)));
    }
}
