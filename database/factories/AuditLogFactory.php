<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Investment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        $eventType = $this->faker->randomElement([
            AuditLog::EVENT_INVESTMENT_CREATED,
            AuditLog::EVENT_INVESTMENT_UPDATED,
            AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            AuditLog::EVENT_WITHDRAWAL_APPROVED,
            AuditLog::EVENT_WITHDRAWAL_REJECTED,
            AuditLog::EVENT_COMMISSION_PAID,
            AuditLog::EVENT_PROFIT_DISTRIBUTED,
            AuditLog::EVENT_TIER_UPGRADED,
            AuditLog::EVENT_LOGIN_ATTEMPT,
            AuditLog::EVENT_PASSWORD_CHANGED,
        ]);

        $isFinancial = in_array($eventType, [
            AuditLog::EVENT_INVESTMENT_CREATED,
            AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            AuditLog::EVENT_WITHDRAWAL_APPROVED,
            AuditLog::EVENT_WITHDRAWAL_REJECTED,
            AuditLog::EVENT_COMMISSION_PAID,
            AuditLog::EVENT_PROFIT_DISTRIBUTED,
        ]);

        return [
            'user_id' => User::factory(),
            'event_type' => $eventType,
            'auditable_type' => $this->getAuditableType($eventType),
            'auditable_id' => $this->faker->numberBetween(1, 1000),
            'old_values' => $this->generateOldValues($eventType),
            'new_values' => $this->generateNewValues($eventType),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'amount' => $isFinancial ? $this->faker->randomFloat(2, 100, 50000) : null,
            'transaction_reference' => $isFinancial ? 'TXN-' . $this->faker->unique()->numerify('######') : null,
            'metadata' => $this->generateMetadata($eventType),
        ];
    }

    public function financial(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => $this->faker->randomElement([
                AuditLog::EVENT_INVESTMENT_CREATED,
                AuditLog::EVENT_WITHDRAWAL_REQUESTED,
                AuditLog::EVENT_COMMISSION_PAID,
                AuditLog::EVENT_PROFIT_DISTRIBUTED,
            ]),
            'amount' => $this->faker->randomFloat(2, 100, 50000),
            'transaction_reference' => 'TXN-' . $this->faker->unique()->numerify('######'),
        ]);
    }

    public function investmentCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => AuditLog::EVENT_INVESTMENT_CREATED,
            'auditable_type' => Investment::class,
            'amount' => $this->faker->randomFloat(2, 500, 10000),
            'transaction_reference' => 'INV-' . $this->faker->unique()->numerify('######'),
            'metadata' => [
                'tier_id' => $this->faker->numberBetween(1, 5),
                'referrer_id' => $this->faker->optional()->numberBetween(1, 100),
            ],
        ]);
    }

    public function withdrawalRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            'auditable_type' => WithdrawalRequest::class,
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'transaction_reference' => 'WD-' . $this->faker->unique()->numerify('######'),
            'metadata' => [
                'type' => $this->faker->randomElement(['full', 'partial', 'emergency']),
                'penalty_amount' => $this->faker->randomFloat(2, 0, 500),
                'net_amount' => $this->faker->randomFloat(2, 100, 4500),
            ],
        ]);
    }

    public function loginAttempt(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => AuditLog::EVENT_LOGIN_ATTEMPT,
            'auditable_type' => null,
            'auditable_id' => null,
            'amount' => null,
            'transaction_reference' => null,
            'metadata' => [
                'email' => $this->faker->email(),
                'successful' => $this->faker->boolean(80),
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
        ]);
    }

    private function getAuditableType(string $eventType): ?string
    {
        return match ($eventType) {
            AuditLog::EVENT_INVESTMENT_CREATED,
            AuditLog::EVENT_INVESTMENT_UPDATED => Investment::class,
            AuditLog::EVENT_WITHDRAWAL_REQUESTED,
            AuditLog::EVENT_WITHDRAWAL_APPROVED,
            AuditLog::EVENT_WITHDRAWAL_REJECTED => WithdrawalRequest::class,
            AuditLog::EVENT_TIER_UPGRADED,
            AuditLog::EVENT_USER_BLOCKED,
            AuditLog::EVENT_USER_UNBLOCKED => User::class,
            default => null,
        };
    }

    private function generateOldValues(string $eventType): ?array
    {
        return match ($eventType) {
            AuditLog::EVENT_INVESTMENT_UPDATED => [
                'status' => 'pending',
                'amount' => $this->faker->randomFloat(2, 100, 1000),
            ],
            AuditLog::EVENT_WITHDRAWAL_APPROVED,
            AuditLog::EVENT_WITHDRAWAL_REJECTED => [
                'status' => 'pending',
            ],
            AuditLog::EVENT_TIER_UPGRADED => [
                'investment_tier_id' => $this->faker->numberBetween(1, 4),
            ],
            AuditLog::EVENT_USER_UNBLOCKED => [
                'is_blocked' => true,
            ],
            default => null,
        };
    }

    private function generateNewValues(string $eventType): ?array
    {
        return match ($eventType) {
            AuditLog::EVENT_INVESTMENT_CREATED => [
                'amount' => $this->faker->randomFloat(2, 500, 10000),
                'status' => 'active',
                'tier_id' => $this->faker->numberBetween(1, 5),
            ],
            AuditLog::EVENT_INVESTMENT_UPDATED => [
                'status' => 'active',
                'amount' => $this->faker->randomFloat(2, 100, 1000),
            ],
            AuditLog::EVENT_WITHDRAWAL_APPROVED => [
                'status' => 'approved',
            ],
            AuditLog::EVENT_WITHDRAWAL_REJECTED => [
                'status' => 'rejected',
            ],
            AuditLog::EVENT_TIER_UPGRADED => [
                'investment_tier_id' => $this->faker->numberBetween(2, 5),
            ],
            AuditLog::EVENT_USER_UNBLOCKED => [
                'is_blocked' => false,
            ],
            default => null,
        };
    }

    private function generateMetadata(string $eventType): ?array
    {
        return match ($eventType) {
            AuditLog::EVENT_INVESTMENT_CREATED => [
                'tier_id' => $this->faker->numberBetween(1, 5),
                'referrer_id' => $this->faker->optional()->numberBetween(1, 100),
            ],
            AuditLog::EVENT_WITHDRAWAL_REQUESTED => [
                'type' => $this->faker->randomElement(['full', 'partial', 'emergency']),
                'penalty_amount' => $this->faker->randomFloat(2, 0, 500),
                'net_amount' => $this->faker->randomFloat(2, 100, 4500),
            ],
            AuditLog::EVENT_WITHDRAWAL_APPROVED => [
                'approved_at' => now()->toISOString(),
            ],
            AuditLog::EVENT_WITHDRAWAL_REJECTED => [
                'rejection_reason' => $this->faker->sentence(),
                'rejected_at' => now()->toISOString(),
            ],
            AuditLog::EVENT_COMMISSION_PAID => [
                'commission_type' => $this->faker->randomElement(['referral', 'matrix', 'bonus']),
                'level' => $this->faker->numberBetween(1, 3),
                'source_investment_id' => $this->faker->numberBetween(1, 1000),
            ],
            AuditLog::EVENT_PROFIT_DISTRIBUTED => [
                'distribution_type' => $this->faker->randomElement(['annual', 'quarterly']),
                'period' => $this->faker->randomElement(['Q1 2024', 'Q2 2024', '2024']),
            ],
            AuditLog::EVENT_TIER_UPGRADED => [
                'from_tier' => $this->faker->numberBetween(1, 4),
                'to_tier' => $this->faker->numberBetween(2, 5),
                'upgraded_at' => now()->toISOString(),
            ],
            AuditLog::EVENT_LOGIN_ATTEMPT => [
                'email' => $this->faker->email(),
                'successful' => $this->faker->boolean(80),
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
            AuditLog::EVENT_PASSWORD_CHANGED => [
                'changed_at' => now()->toISOString(),
            ],
            default => null,
        };
    }
}