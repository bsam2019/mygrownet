<?php

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<BizBoostCampaignModel>
 */
class BizBoostCampaignFactory extends Factory
{
    protected $model = BizBoostCampaignModel::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 week');
        $durationDays = $this->faker->randomElement([3, 7, 14, 21, 30]);
        $endDate = (clone $startDate)->modify("+{$durationDays} days");

        return [
            'business_id' => BizBoostBusinessModel::factory(),
            'name' => $this->faker->randomElement([
                'Holiday Sale Campaign',
                'New Arrivals Promotion',
                'Weekend Flash Sale',
                'Customer Appreciation Week',
                'Back to School Special',
                'End of Month Clearance',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'objective' => $this->faker->randomElement([
                'increase_sales',
                'promote_stock',
                'announce_discount',
                'bring_back_customers',
                'grow_followers',
            ]),
            'status' => 'draft',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'duration_days' => $durationDays,
            'target_platforms' => $this->faker->randomElements(['facebook', 'instagram'], $this->faker->numberBetween(1, 2)),
            'campaign_config' => [
                'template_id' => null,
                'auto_generate' => false,
                'posting_times' => ['09:00', '12:00', '18:00'],
            ],
            'posts_created' => 0,
            'posts_published' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => now()->subDays(2)->format('Y-m-d'),
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => now()->subDays(14)->format('Y-m-d'),
            'end_date' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    public function forBusiness(BizBoostBusinessModel $business): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business->id,
        ]);
    }
}
