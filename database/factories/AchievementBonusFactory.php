<?php

namespace Database\Factories;

use App\Models\AchievementBonus;
use App\Models\User;
use App\Models\Achievement;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AchievementBonus>
 */
class AchievementBonusFactory extends Factory
{
    protected $model = AchievementBonus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bonusTypes = array_keys(AchievementBonus::BONUS_TYPES);
        $statuses = array_keys(AchievementBonus::STATUSES);
        $earnedAt = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'user_id' => User::factory(),
            'achievement_id' => Achievement::factory(),
            'bonus_type' => $this->faker->randomElement($bonusTypes),
            'amount' => $this->faker->randomFloat(2, 50, 2500),
            'status' => $this->faker->randomElement($statuses),
            'earned_at' => $earnedAt,
            'paid_at' => null,
            'payment_transaction_id' => null,
            'payment_method' => null,
            'tier_at_earning' => $this->faker->randomElement(['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite']),
            'team_volume_at_earning' => $this->faker->optional()->randomFloat(2, 10000, 500000),
            'active_referrals_at_earning' => $this->faker->optional()->numberBetween(1, 50),
            'description' => $this->faker->sentence(),
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the bonus is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
            'payment_transaction_id' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that the bonus is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween($attributes['earned_at'], 'now'),
            'payment_method' => $this->faker->randomElement(['wallet', 'mobile_money']),
        ]);
    }

    /**
     * Indicate that the bonus is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'paid_at' => null,
            'payment_transaction_id' => null,
            'payment_method' => null,
            'metadata' => [
                'cancellation_reason' => $this->faker->sentence(),
                'cancelled_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Indicate that the bonus is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'earned_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'paid_at' => null,
            'payment_transaction_id' => null,
            'payment_method' => null,
        ]);
    }

    /**
     * Indicate that this is a tier advancement bonus.
     */
    public function tierAdvancement(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'tier_advancement',
            'description' => 'Tier advancement bonus for reaching ' . $attributes['tier_at_earning'],
        ]);
    }

    /**
     * Indicate that this is a performance bonus.
     */
    public function performance(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'performance',
            'team_volume_at_earning' => $this->faker->randomFloat(2, 25000, 500000),
            'description' => 'Performance bonus for achieving high team volume',
        ]);
    }

    /**
     * Indicate that this is a leadership bonus.
     */
    public function leadership(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'leadership',
            'active_referrals_at_earning' => $this->faker->numberBetween(5, 50),
            'team_volume_at_earning' => $this->faker->randomFloat(2, 50000, 500000),
            'description' => 'Leadership bonus for building strong team',
        ]);
    }

    /**
     * Indicate that this is a milestone bonus.
     */
    public function milestone(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'milestone',
            'description' => 'Milestone achievement bonus',
        ]);
    }

    /**
     * Indicate that this is a special event bonus.
     */
    public function special(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'special',
            'description' => 'Special event bonus',
        ]);
    }

    /**
     * Indicate that the bonus is eligible for payment.
     */
    public function eligibleForPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'earned_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }

    /**
     * Indicate that the bonus was recently earned.
     */
    public function recentlyEarned(): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_at' => $this->faker->dateTimeBetween('-12 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the bonus is overdue for payment.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'earned_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
        ]);
    }

    /**
     * Create bonus for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'tier_at_earning' => $user->membershipTier?->name ?? 'Bronze',
        ]);
    }

    /**
     * Create bonus for specific achievement.
     */
    public function forAchievement(Achievement $achievement): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_id' => $achievement->id,
            'bonus_type' => $achievement->category ?? 'milestone',
            'description' => "Bonus for {$achievement->name} achievement",
        ]);
    }

    /**
     * Create bonus with specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Create bonus earned on specific date.
     */
    public function earnedAt($date): static
    {
        return $this->state(fn (array $attributes) => [
            'earned_at' => $date,
        ]);
    }

    /**
     * Create bonus with payment transaction.
     */
    public function withPaymentTransaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => $this->faker->dateTimeBetween($attributes['earned_at'], 'now'),
            'payment_transaction_id' => PaymentTransaction::factory()->completed()->bonusPayment(),
            'payment_method' => 'wallet',
        ]);
    }

    /**
     * Create bonus for bronze tier.
     */
    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_at_earning' => 'Bronze',
            'amount' => $this->faker->randomFloat(2, 50, 200),
        ]);
    }

    /**
     * Create bonus for silver tier.
     */
    public function silver(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_at_earning' => 'Silver',
            'amount' => $this->faker->randomFloat(2, 100, 400),
        ]);
    }

    /**
     * Create bonus for gold tier.
     */
    public function gold(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_at_earning' => 'Gold',
            'amount' => $this->faker->randomFloat(2, 200, 800),
        ]);
    }

    /**
     * Create bonus for diamond tier.
     */
    public function diamond(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_at_earning' => 'Diamond',
            'amount' => $this->faker->randomFloat(2, 500, 1500),
        ]);
    }

    /**
     * Create bonus for elite tier.
     */
    public function elite(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_at_earning' => 'Elite',
            'amount' => $this->faker->randomFloat(2, 1000, 2500),
        ]);
    }

    /**
     * Create bonus with high team volume.
     */
    public function highVolume(): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus_type' => 'performance',
            'team_volume_at_earning' => $this->faker->randomFloat(2, 100000, 500000),
            'active_referrals_at_earning' => $this->faker->numberBetween(10, 50),
        ]);
    }

    /**
     * Create bonus with metadata.
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }
}