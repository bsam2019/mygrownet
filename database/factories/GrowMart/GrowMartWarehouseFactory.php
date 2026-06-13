<?php

namespace Database\Factories\GrowMart;

use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrowMartWarehouseFactory extends Factory
{
    protected $model = GrowMartWarehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Warehouse',
            'province' => 'Lusaka',
            'city' => 'Lusaka',
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
