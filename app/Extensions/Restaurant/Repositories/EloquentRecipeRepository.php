<?php

namespace App\Extensions\Restaurant\Repositories;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Restaurant\Entities\Recipe;
use App\Extensions\Restaurant\Entities\RecipeIngredient;
use App\Extensions\Restaurant\Models\SaRecipeModel;
use App\Extensions\Restaurant\ValueObjects\RecipeId;
use DateTimeImmutable;

class EloquentRecipeRepository implements RecipeRepositoryInterface
{
    public function findById(RecipeId $id): ?Recipe
    {
        $model = SaRecipeModel::with('ingredients')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompany(CompanyId $companyId): array
    {
        return SaRecipeModel::with('ingredients')->where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(Recipe $recipe): Recipe
    {
        $data = $recipe->toArray();
        $ingredientsData = $data['ingredients'] ?? [];
        unset($data['id'], $data['ingredients'], $data['created_at'], $data['updated_at']);

        if ($recipe->id() === 0) {
            $model = SaRecipeModel::create($data);
            foreach ($ingredientsData as $ing) {
                $ing['sa_recipe_id'] = $model->id;
                $model->ingredients()->create($ing);
            }
        } else {
            $model = SaRecipeModel::find($recipe->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh()->load('ingredients'));
    }

    public function delete(RecipeId $id): void
    {
        SaRecipeModel::find($id->toInt())?->delete();
    }

    private function toDomainEntity(SaRecipeModel $model): Recipe
    {
        $ingredients = $model->ingredients->map(fn($i) => new RecipeIngredient(
            itemId: $i->sa_item_id, quantity: (float) $i->quantity, uom: $i->uom,
            wasteFactor: (float) $i->waste_factor, isSubstitutable: (bool) $i->is_substitutable,
            sortOrder: $i->sort_order,
        ))->all();

        return Recipe::reconstitute(
            id: RecipeId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            itemId: ItemId::fromInt($model->sa_item_id), name: $model->name,
            yieldQuantity: (float) $model->yield_quantity, yieldUom: $model->yield_uom,
            difficulty: $model->difficulty, prepTime: $model->prep_time_minutes,
            cookTime: $model->cook_time_minutes, instructions: $model->instructions,
            allergens: $model->allergens, dietaryLabels: $model->dietary_labels,
            status: $model->status, ingredients: $ingredients,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
