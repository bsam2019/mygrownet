<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentureDividendModelFactory extends Factory
{
    protected $model = VentureDividendModel::class;

    public function definition(): array
    {
        return [
            'venture_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel::factory(),
            'shareholder_id' => \App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel::factory(),
            'dividend_period' => 'Q1-' . $this->faker->year(),
            'declaration_date' => now()->subDays(5),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'equity_percentage_at_payment' => $this->faker->randomFloat(4, 0.5, 50),
            'status' => 'declared',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'paid',
            'payment_date' => now(),
            'payment_method' => 'wallet',
            'payment_reference' => 'DIV-' . strtoupper($this->faker->bothify('??##??##')),
            'paid_at' => now(),
        ]);
    }
}
