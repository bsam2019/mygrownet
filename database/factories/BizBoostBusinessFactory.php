<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BizBoostBusinessFactory extends Factory
{
    protected $model = BizBoostBusinessModel::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraph(),
            'logo_path' => null,
            'industry' => $this->faker->randomElement([
                'boutique', 'salon', 'barbershop', 'restaurant', 
                'grocery', 'hardware', 'photography', 'mobile_money', 
                'electronics', 'general'
            ]),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->state(),
            'country' => 'Zambia',
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->optional()->url(),
            'timezone' => 'Africa/Lusaka',
            'locale' => 'en',
            'currency' => 'ZMW',
            'social_links' => [
                'facebook' => $this->faker->optional()->url(),
                'instagram' => $this->faker->optional()->url(),
            ],
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '17:00'],
                'tuesday' => ['open' => '08:00', 'close' => '17:00'],
                'wednesday' => ['open' => '08:00', 'close' => '17:00'],
                'thursday' => ['open' => '08:00', 'close' => '17:00'],
                'friday' => ['open' => '08:00', 'close' => '17:00'],
                'saturday' => ['open' => '09:00', 'close' => '13:00'],
                'sunday' => null,
            ],
            'settings' => [],
            'is_active' => true,
            'onboarding_completed' => true,
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forIndustry(string $industry): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => $industry,
        ]);
    }
}
