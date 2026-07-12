<?php

namespace Database\Factories\GrowMart;

use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GrowMartProductFactory extends Factory
{
    protected $model = GrowMartProduct::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement(['kg', 'piece', 'litre', 'bunch', 'packet']),
            'price' => fake()->numberBetween(500, 20000),
            'compare_price' => null,
            'category_id' => GrowMartCategory::factory(),
            'status' => 'active',
        ];
    }
}
