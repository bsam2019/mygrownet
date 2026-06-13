<?php

namespace Database\Factories\GrowMart;

use App\Models\GrowMart\GrowMartCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GrowMartCategoryFactory extends Factory
{
    protected $model = GrowMartCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Fresh Vegetables', 'Fruits', 'Dairy & Eggs', 'Meat & Poultry',
            'Fish & Seafood', 'Pantry Staples', 'Beverages', 'Snacks',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
