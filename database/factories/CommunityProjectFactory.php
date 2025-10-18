<?php

namespace Database\Factories;

use App\Models\CommunityProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommunityProjectFactory extends Factory
{
    protected $model = CommunityProject::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(3);
        $targetAmount = $this->faker->numberBetween(50000, 500000);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(2),
            'detailed_description' => $this->faker->paragraphs(5, true),
            'type' => $this->faker->randomElement(['real_estate', 'agriculture', 'sme', 'digital', 'infrastructure']),
            'category' => $this->faker->randomElement(['property_development', 'farming', 'business_venture', 'technology', 'community_infrastructure']),
            'target_amount' => $targetAmount,
            'current_amount' => $this->faker->numberBetween(0, $targetAmount * 0.8),
            'minimum_contribution' => $this->faker->randomElement([500, 1000, 2500, 5000]),
            'maximum_contribution' => $this->faker->optional()->numberBetween(50000, 200000),
            'expected_annual_return' => $this->faker->randomFloat(2, 8, 20),
            'project_duration_months' => $this->faker->numberBetween(12, 60),
            'funding_start_date' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'funding_end_date' => $this->faker->dateTimeBetween('+31 days', '+90 days'),
            'project_start_date' => $this->faker->optional()->dateTimeBetween('+91 days', '+120 days'),
            'expected_completion_date' => $this->faker->optional()->dateTimeBetween('+121 days', '+2 years'),
            'status' => $this->faker->randomElement(['planning', 'funding', 'active', 'completed']),
            'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'requires_voting' => $this->faker->boolean(80),
            'is_featured' => $this->faker->boolean(20),
            'auto_approve_contributions' => $this->faker->boolean(30),
            'required_membership_tiers' => $this->faker->randomElement([
                [],
                ['Bronze'],
                ['Silver', 'Gold'],
                ['Gold', 'Diamond', 'Elite'],
                ['Diamond', 'Elite']
            ]),
            'tier_contribution_limits' => $this->generateTierLimits(),
            'tier_voting_weights' => $this->generateVotingWeights(),
            'project_manager_id' => $this->faker->optional()->randomElement([User::factory()]),
            'created_by' => User::factory(),
            'project_milestones' => $this->generateMilestones(),
            'risk_factors' => $this->generateRiskFactors(),
            'success_metrics' => $this->generateSuccessMetrics(),
            'featured_image_url' => $this->faker->optional()->imageUrl(800, 600, 'business'),
            'gallery_images' => $this->faker->optional()->randomElements([
                $this->faker->imageUrl(400, 300, 'business'),
                $this->faker->imageUrl(400, 300, 'business'),
                $this->faker->imageUrl(400, 300, 'business')
            ], $this->faker->numberBetween(0, 3)),
            'documents' => $this->generateDocuments(),
            'total_contributors' => $this->faker->numberBetween(0, 50),
            'total_votes' => $this->faker->numberBetween(0, 100),
            'community_approval_rating' => $this->faker->randomFloat(2, 60, 95)
        ];
    }

    public function funding(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'funding',
            'funding_start_date' => now()->subDays(5),
            'funding_end_date' => now()->addDays(25)
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'project_start_date' => now()->subDays(30),
            'current_amount' => $attributes['target_amount'] ?? 100000
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_completion_date' => now()->subDays(10),
            'current_amount' => $attributes['target_amount'] ?? 100000
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'featured_image_url' => $this->faker->imageUrl(800, 600, 'business')
        ]);
    }

    public function forTier(string $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'required_membership_tiers' => [$tier]
        ]);
    }

    public function realEstate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'real_estate',
            'category' => 'property_development',
            'risk_level' => 'medium',
            'expected_annual_return' => $this->faker->randomFloat(2, 10, 15)
        ]);
    }

    public function agriculture(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'agriculture',
            'category' => 'farming',
            'risk_level' => 'high',
            'expected_annual_return' => $this->faker->randomFloat(2, 15, 25)
        ]);
    }

    public function sme(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sme',
            'category' => 'business_venture',
            'risk_level' => 'medium',
            'expected_annual_return' => $this->faker->randomFloat(2, 12, 18)
        ]);
    }

    private function generateTierLimits(): array
    {
        return [
            'Bronze' => $this->faker->numberBetween(5000, 15000),
            'Silver' => $this->faker->numberBetween(15000, 50000),
            'Gold' => $this->faker->numberBetween(50000, 150000),
            'Diamond' => $this->faker->numberBetween(150000, 500000),
            'Elite' => null // No limit
        ];
    }

    private function generateVotingWeights(): array
    {
        return [
            'Bronze' => 1.0,
            'Silver' => 1.2,
            'Gold' => 1.5,
            'Diamond' => 2.0,
            'Elite' => 3.0
        ];
    }

    private function generateMilestones(): array
    {
        return [
            [
                'name' => 'Project Planning Complete',
                'description' => 'Complete all planning and documentation',
                'target_date' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
                'status' => 'pending'
            ],
            [
                'name' => 'Phase 1 Implementation',
                'description' => 'Begin first phase of project implementation',
                'target_date' => $this->faker->dateTimeBetween('+3 months', '+6 months')->format('Y-m-d'),
                'status' => 'pending'
            ],
            [
                'name' => 'Mid-project Review',
                'description' => 'Comprehensive review of project progress',
                'target_date' => $this->faker->dateTimeBetween('+6 months', '+12 months')->format('Y-m-d'),
                'status' => 'pending'
            ]
        ];
    }

    private function generateRiskFactors(): array
    {
        return [
            'Market volatility may affect returns',
            'Regulatory changes could impact project timeline',
            'Weather conditions may affect agricultural projects',
            'Economic conditions may influence demand'
        ];
    }

    private function generateSuccessMetrics(): array
    {
        return [
            'ROI target: ' . $this->faker->randomFloat(2, 10, 20) . '%',
            'Project completion within timeline',
            'Community satisfaction rating > 80%',
            'Environmental impact compliance'
        ];
    }

    private function generateDocuments(): array
    {
        return [
            [
                'name' => 'Business Plan',
                'type' => 'pdf',
                'url' => '/documents/business-plan.pdf',
                'uploaded_at' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s')
            ],
            [
                'name' => 'Financial Projections',
                'type' => 'xlsx',
                'url' => '/documents/financial-projections.xlsx',
                'uploaded_at' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s')
            ]
        ];
    }
}