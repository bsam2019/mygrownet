<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureShareholderModelFactory extends Factory
{
    protected $model = VentureShareholderModel::class;

    public function definition(): array
    {
        return [
            'venture_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory(),
            'user_id' => User::factory(),
            'investment_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel::factory(),
            'total_investment' => $this->faker->randomFloat(2, 1000, 50000),
            'shares_owned' => $this->faker->numberBetween(10, 500),
            'equity_percentage' => $this->faker->randomFloat(4, 0.5, 50),
            'certificate_number' => 'SH-' . strtoupper($this->faker->bothify('??##??##')),
            'registration_date' => now(),
            'status' => 'active',
        ];
    }
}
