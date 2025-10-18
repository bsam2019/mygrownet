<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph(3),
            'learning_objectives' => $this->faker->paragraph(2),
            'category' => $this->faker->randomElement([
                'financial_literacy',
                'business_skills',
                'life_skills',
                'investment_strategies',
                'mlm_training',
                'leadership_development'
            ]),
            'difficulty_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'estimated_duration_minutes' => $this->faker->numberBetween(30, 240),
            'thumbnail_url' => $this->faker->imageUrl(400, 300, 'education'),
            'required_subscription_packages' => [],
            'required_membership_tiers' => $this->faker->randomElement([
                [],
                ['Bronze'],
                ['Bronze', 'Silver'],
                ['Silver', 'Gold'],
                ['Gold', 'Diamond'],
                ['Diamond', 'Elite'],
                ['Elite']
            ]),
            'is_premium' => $this->faker->boolean(30),
            'certificate_eligible' => $this->faker->boolean(60),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'created_by' => User::factory(),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'order' => $this->faker->numberBetween(1, 100),
            'content_update_frequency' => $this->faker->randomElement(['weekly', 'monthly', 'quarterly']),
            'last_content_update' => $this->faker->optional(0.7)->dateTimeBetween('-3 months', 'now'),
            'tier_specific_content' => $this->generateTierSpecificContent(),
            'monthly_content_schedule' => $this->generateMonthlySchedule()
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now')
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null
        ]);
    }

    public function forTier(string $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => [$tier]
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'certificate_eligible' => true
        ]);
    }

    public function withCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category
        ]);
    }

    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'beginner',
            'estimated_duration_minutes' => $this->faker->numberBetween(30, 90)
        ]);
    }

    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'advanced',
            'estimated_duration_minutes' => $this->faker->numberBetween(120, 300),
            'is_premium' => true
        ]);
    }

    private function generateTierSpecificContent(): array
    {
        $tiers = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        $content = [];

        foreach ($this->faker->randomElements($tiers, $this->faker->numberBetween(1, 3)) as $tier) {
            $content[$tier] = [
                'additional_resources' => $this->faker->words(5),
                'tier_specific_modules' => $this->faker->numberBetween(1, 5),
                'bonus_content' => $this->faker->sentence()
            ];
        }

        return $content;
    }

    private function generateMonthlySchedule(): array
    {
        $schedule = [];
        $months = ['2025-01', '2025-02', '2025-03', '2025-04', '2025-05', '2025-06'];

        foreach ($this->faker->randomElements($months, $this->faker->numberBetween(1, 3)) as $month) {
            $schedule[$month] = [
                'content_updates' => $this->faker->words(3),
                'new_modules' => $this->faker->numberBetween(1, 3),
                'updated_resources' => $this->faker->numberBetween(1, 5)
            ];
        }

        return $schedule;
    }
}