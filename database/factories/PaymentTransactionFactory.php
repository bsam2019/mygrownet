<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_keys(PaymentTransaction::TYPES);
        $statuses = array_keys(PaymentTransaction::STATUSES);
        $paymentMethods = array_keys(PaymentTransaction::PAYMENT_METHODS);

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement($statuses),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'payment_details' => [
                'phone_number' => $this->faker->phoneNumber,
                'commission_ids' => [$this->faker->numberBetween(1, 100)],
                'commission_count' => $this->faker->numberBetween(1, 5)
            ],
            'reference' => 'MGN-' . strtoupper($this->faker->lexify('???')) . '-' . $this->faker->numberBetween(100000, 999999),
            'external_reference' => $this->faker->optional()->uuid,
            'payment_response' => $this->faker->optional()->randomElement([
                ['success' => true, 'provider' => 'mtn'],
                ['success' => false, 'error' => 'Payment failed']
            ]),
            'failure_reason' => $this->faker->optional()->sentence,
            'retry_count' => $this->faker->numberBetween(0, 3),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'failed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'failed_at' => null,
            'external_reference' => $this->faker->uuid,
            'payment_response' => [
                'success' => true,
                'provider' => $this->faker->randomElement(['mtn', 'airtel', 'zamtel']),
                'external_reference' => $this->faker->uuid
            ]
        ]);
    }

    /**
     * Indicate that the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'completed_at' => null,
            'failure_reason' => $this->faker->randomElement([
                'Insufficient funds',
                'Invalid phone number',
                'Network timeout',
                'Provider error'
            ]),
            'payment_response' => [
                'success' => false,
                'error' => $this->faker->sentence
            ]
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'failed_at' => null,
            'external_reference' => null,
            'payment_response' => null,
            'failure_reason' => null
        ]);
    }

    /**
     * Indicate that this is a commission payment.
     */
    public function commissionPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'commission_payment',
            'payment_details' => [
                'phone_number' => '+260' . $this->faker->randomElement(['96', '97', '95']) . $this->faker->numerify('#######'),
                'commission_ids' => $this->faker->randomElements(range(1, 100), $this->faker->numberBetween(1, 5)),
                'commission_count' => $this->faker->numberBetween(1, 5)
            ]
        ]);
    }

    /**
     * Indicate that this is a subscription payment.
     */
    public function subscriptionPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'subscription_payment',
            'payment_details' => [
                'phone_number' => '+260' . $this->faker->randomElement(['96', '97', '95']) . $this->faker->numerify('#######'),
                'subscription_tier' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'diamond', 'elite']),
                'billing_period' => 'monthly'
            ]
        ]);
    }

    /**
     * Indicate that this is a bonus payment.
     */
    public function bonusPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bonus_payment',
            'payment_details' => [
                'phone_number' => '+260' . $this->faker->randomElement(['96', '97', '95']) . $this->faker->numerify('#######'),
                'bonus_type' => $this->faker->randomElement(['performance', 'tier_advancement', 'leadership']),
                'achievement_id' => $this->faker->numberBetween(1, 50)
            ]
        ]);
    }

    /**
     * Indicate that this transaction can be retried.
     */
    public function retryable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'retry_count' => $this->faker->numberBetween(0, 2), // Less than max retries
            'failed_at' => $this->faker->dateTimeBetween('-3 days', 'now'), // Within retry window
            'failure_reason' => 'Network timeout - retryable'
        ]);
    }

    /**
     * Indicate that this transaction has exceeded retry limit.
     */
    public function maxRetriesExceeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'retry_count' => 3, // Max retries exceeded
            'failed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'failure_reason' => 'Max retries exceeded'
        ]);
    }

    /**
     * Indicate that this transaction is outside retry window.
     */
    public function outsideRetryWindow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'retry_count' => 1,
            'failed_at' => $this->faker->dateTimeBetween('-2 weeks', '-8 days'), // Outside 7-day window
            'failure_reason' => 'Outside retry window'
        ]);
    }

    /**
     * Create transaction with specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount
        ]);
    }

    /**
     * Create transaction for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'payment_details' => array_merge(
                $attributes['payment_details'] ?? [],
                ['phone_number' => $user->phone_number]
            )
        ]);
    }
}