<?php

namespace Database\Factories\GrowMart;

use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartProduct;
use App\Models\GrowMart\GrowMartWarehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrowMartInventoryFactory extends Factory
{
    protected $model = GrowMartInventory::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => GrowMartWarehouse::factory(),
            'product_id' => GrowMartProduct::factory(),
            'quantity' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 10,
        ];
    }
}
