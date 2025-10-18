<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = array_keys(Subscription::STATUSES);
        $startedAt = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'user_id' => User::factory(),
            'tier_id' => InvestmentTier::factory(),
            'status' => $this->faker->randomElement($statuses),
            'started_at' => $startedAt,
            'next_billing_date' => $this->faker->dateTimeBetween($startedAt, '+1 month'),
            'last_payment_at' => $this->faker->optional()->dateTimeBetween($startedAt, 'now'),
            'last_payment_amount' => $this->faker->optional()->randomFloat(2, 50, 1000),
            'payment_transaction_id' => null,
            'failed_payment_attempts' => $this->faker->numberBetween(0, 3),
            'suspended_at' => null,
            'suspension_reason' => null,
            'downgraded_at' => null,
            'downgrade_reason' => null,
            'upgraded_at' => null,
            'upgrade_reason' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }

    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Indicate that the subscription is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'suspended_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'suspension_reason' => $this->faker->randomElement(array_keys(Subscription::SUSPENSION_REASONS)),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Indicate that the subscription is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'cancellation_reason' => $this->faker->randomElement(array_keys(Subscription::CANCELLATION_REASONS)),
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);
    }

    /**
     * Indicate that the subscription is due for billing.
     */
    public function dueForBilling(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_billing_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the subscription is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'next_billing_date' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
            'failed_payment_attempts' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the subscription has failed payment attempts.
     */
    public function withFailedPayments(): static
    {
        return $this->state(fn (array $attributes) => [
            'failed_payment_attempts' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the subscription was recently downgraded.
     */
    public function downgraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'downgraded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'downgrade_reason' => $this->faker->randomElement(['failed_payments', 'user_request']),
            'failed_payment_attempts' => $this->faker->numberBetween(2, 3),
        ]);
    }

    /**
     * Indicate that the subscription was recently upgraded.
     */
    public function upgraded(): static
    {
        return $this->state(fn (array $attributes) => [
            'upgraded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'upgrade_reason' => $this->faker->randomElement(['user_requested', 'tier_qualification']),
        ]);
    }

    /**
     * Indicate that the subscription has a recent payment.
     */
    public function withRecentPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_payment_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'last_payment_amount' => $this->faker->randomFloat(2, 50, 500),
            'failed_payment_attempts' => 0,
        ]);
    }

    /**
     * Create subscription for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create subscription for specific tier.
     */
    public function forTier(InvestmentTier $tier): static
    {
        return $this->state(fn (array $attributes) => [
            'tier_id' => $tier->id,
        ]);
    }

    /**
     * Create subscription with specific billing date.
     */
    public function withBillingDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'next_billing_date' => $date,
        ]);
    }

    /**
     * Create subscription with specific failed attempts.
     */
    public function withFailedAttempts(int $attempts): static
    {
        return $this->state(fn (array $attributes) => [
            'failed_payment_attempts' => $attempts,
        ]);
    }

    /**
     * Create subscription that started on specific date.
     */
    public function startedAt($date): static
    {
        return $this->state(fn (array $attributes) => [
            'started_at' => $date,
        ]);
    }

    /**
     * Create subscription with payment transaction.
     */
    public function withPaymentTransaction(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_transaction_id' => PaymentTransaction::factory()->completed()->subscriptionPayment(),
        ]);
    }

    /**
     * Create subscription that's at risk of suspension.
     */
    public function atRiskOfSuspension(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'failed_payment_attempts' => 2, // One away from max (assuming max is 3)
            'next_billing_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create subscription with specific monthly fee.
     */
    public function withMonthlyFee(float $fee): static
    {
        return $this->afterCreating(function (Subscription $subscription) use ($fee) {
            $subscription->tier->update(['monthly_fee' => $fee]);
        });
    }

    /**
     * Create subscription for bronze tier.
     */
    public function bronze(): static
    {
        return $this->afterMaking(function (Subscription $subscription) {
            $subscription->tier = InvestmentTier::factory()->create([
                'name' => 'Bronze',
                'monthly_fee' => 50.00
            ]);
        });
    }

    /**
     * Create subscription for silver tier.
     */
    public function silver(): static
    {
        return $this->afterMaking(function (Subscription $subscription) {
            $subscription->tier = InvestmentTier::factory()->create([
                'name' => 'Silver',
                'monthly_fee' => 100.00
            ]);
        });
    }

    /**
     * Create subscription for gold tier.
     */
    public function gold(): static
    {
        return $this->afterMaking(function (Subscription $subscription) {
            $subscription->tier = InvestmentTier::factory()->create([
                'name' => 'Gold',
                'monthly_fee' => 200.00
            ]);
        });
    }

    /**
     * Create subscription for diamond tier.
     */
    public function diamond(): static
    {
        return $this->afterMaking(function (Subscription $subscription) {
            $subscription->tier = InvestmentTier::factory()->create([
                'name' => 'Diamond',
                'monthly_fee' => 500.00
            ]);
        });
    }

    /**
     * Create subscription for elite tier.
     */
    public function elite(): static
    {
        return $this->afterMaking(function (Subscription $subscription) {
            $subscription->tier = InvestmentTier::factory()->create([
                'name' => 'Elite',
                'monthly_fee' => 1000.00
            ]);
        });
    }
}