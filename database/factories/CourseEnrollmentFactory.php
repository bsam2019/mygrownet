<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseEnrollmentFactory extends Factory
{
    protected $model = CourseEnrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'enrolled_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'tier_at_enrollment' => $this->faker->randomElement(['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite']),
            'progress_percentage' => $this->faker->randomFloat(2, 0, 100),
            'completed_at' => $this->faker->optional(0.3)->dateTimeBetween('-3 months', 'now'),
            'certificate_issued_at' => $this->faker->optional(0.2)->dateTimeBetween('-2 months', 'now'),
            'status' => $this->faker->randomElement(['enrolled', 'in_progress', 'completed', 'dropped'])
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100.00,
            'completed_at' => $this->faker->dateTimeBetween('-2 months', 'now')
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'progress_percentage' => $this->faker->randomFloat(2, 10, 90),
            'completed_at' => null
        ]);
    }

    public function withCertificate(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'progress_percentage' => 100.00,
            'completed_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'certificate_issued_at' => $this->faker->dateTimeBetween('-1 month', 'now')
        ]);
    }
}