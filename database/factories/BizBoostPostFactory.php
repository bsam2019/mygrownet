<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BizBoostPostFactory extends Factory
{
    protected $model = BizBoostPostModel::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'scheduled', 'published']);
        
        return [
            'business_id' => BizBoostBusinessModel::factory(),
            'title' => $this->faker->optional()->sentence(4),
            'caption' => $this->faker->paragraph(2),
            'status' => $status,
            'scheduled_at' => $status === 'scheduled' ? $this->faker->dateTimeBetween('now', '+1 month') : null,
            'published_at' => $status === 'published' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'platform_targets' => $this->faker->randomElements(['facebook', 'instagram'], $this->faker->numberBetween(0, 2)),
            'post_type' => $this->faker->randomElement(['standard', 'story', 'reel']),
            'external_ids' => null,
            'analytics' => null,
            'error_message' => null,
            'retry_count' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'scheduled_at' => null,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'published_at' => null,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'scheduled_at' => null,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Failed to publish: API error',
            'retry_count' => $this->faker->numberBetween(1, 3),
        ]);
    }
}
