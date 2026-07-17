<?php

declare(strict_types=1);

namespace App\Extensions\Restaurant\Entities;

use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Extensions\Restaurant\ValueObjects\RecipeId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Recipe implements Arrayable
{
    /** @param RecipeIngredient[] $ingredients */
    private function __construct(
        private RecipeId $id, private CompanyId $companyId, private ItemId $itemId,
        private string $name, private float $yieldQuantity, private string $yieldUom,
        private string $difficulty, private ?int $prepTime, private ?int $cookTime,
        private ?string $instructions, private ?array $allergens, private ?array $dietaryLabels,
        private string $status, private array $ingredients,
        private DateTimeImmutable $createdAt, private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, ItemId $itemId, string $name, float $yieldQuantity, array $ingredients = [], string $yieldUom = 'portions', ?string $instructions = null): self
    {
        return new self(RecipeId::generate(), $companyId, $itemId, $name, $yieldQuantity, $yieldUom, 'easy', null, null, $instructions, null, null, 'active', $ingredients, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(RecipeId $id, CompanyId $companyId, ItemId $itemId, string $name, float $yieldQuantity, string $yieldUom, string $difficulty, ?int $prepTime, ?int $cookTime, ?string $instructions, ?array $allergens, ?array $dietaryLabels, string $status, array $ingredients, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $itemId, $name, $yieldQuantity, $yieldUom, $difficulty, $prepTime, $cookTime, $instructions, $allergens, $dietaryLabels, $status, $ingredients, $createdAt, $updatedAt);
    }

    public function id(): int { return $this->id->toInt(); }
    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(), 'sa_company_id' => $this->companyId->toInt(),
            'sa_item_id' => $this->itemId->toInt(), 'name' => $this->name,
            'yield_quantity' => $this->yieldQuantity, 'yield_uom' => $this->yieldUom,
            'difficulty' => $this->difficulty, 'prep_time_minutes' => $this->prepTime,
            'cook_time_minutes' => $this->cookTime, 'instructions' => $this->instructions,
            'allergens' => $this->allergens, 'dietary_labels' => $this->dietaryLabels,
            'status' => $this->status,
            'ingredients' => array_map(fn($i) => $i->toArray(), $this->ingredients),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

class RecipeIngredient implements Arrayable
{
    public function __construct(
        public readonly int $itemId, public readonly float $quantity, public readonly string $uom = 'each',
        public readonly float $wasteFactor = 0, public readonly bool $isSubstitutable = false,
        public readonly int $sortOrder = 0,
    ) {}

    public function toArray(): array
    {
        return ['sa_item_id' => $this->itemId, 'quantity' => $this->quantity, 'uom' => $this->uom, 'waste_factor' => $this->wasteFactor, 'is_substitutable' => $this->isSubstitutable, 'sort_order' => $this->sortOrder];
    }
}
