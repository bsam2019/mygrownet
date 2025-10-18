<?php

namespace Database\Factories;

use App\Models\SuspiciousActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuspiciousActivityFactory extends Factory
{
    protected $model = SuspiciousActivity::class;

    public function definition(): array
    {
        $activityType = $this->faker->randomElement([
            SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
            SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
            SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN,
            SuspiciousActivity::TYPE_MULTIPLE_DEVICES,
            SuspiciousActivity::TYPE_GEOGRAPHIC_ANOMALY,
        ]);

        $severity = $this->faker->randomElement([
            SuspiciousActivity::SEVERITY_LOW,
            SuspiciousActivity::SEVERITY_MEDIUM,
            SuspiciousActivity::SEVERITY_HIGH,
            SuspiciousActivity::SEVERITY_CRITICAL,
        ]);

        return [
            'user_id' => User::factory(),
            'activity_type' => $activityType,
            'severity' => $severity,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'activity_data' => $this->generateActivityData($activityType),
            'detection_rules' => $this->generateDetectionRules($activityType),
            'is_resolved' => $this->faker->boolean(30),
            'resolution_action' => null,
            'admin_notes' => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_resolved' => true,
            'resolution_action' => $this->faker->randomElement([
                SuspiciousActivity::RESOLUTION_BLOCKED,
                SuspiciousActivity::RESOLUTION_WARNED,
                SuspiciousActivity::RESOLUTION_CLEARED,
                SuspiciousActivity::RESOLUTION_MONITORING,
            ]),
            'admin_notes' => $this->faker->sentence(),
            'resolved_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'resolved_by' => User::factory(),
        ]);
    }

    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_resolved' => false,
            'resolution_action' => null,
            'admin_notes' => null,
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => SuspiciousActivity::SEVERITY_CRITICAL,
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => SuspiciousActivity::SEVERITY_HIGH,
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => SuspiciousActivity::SEVERITY_MEDIUM,
        ]);
    }

    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity' => SuspiciousActivity::SEVERITY_LOW,
        ]);
    }

    public function duplicateAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT,
            'activity_data' => $this->generateActivityData(SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT),
            'detection_rules' => $this->generateDetectionRules(SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT),
        ]);
    }

    public function rapidInvestments(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => SuspiciousActivity::TYPE_RAPID_INVESTMENTS,
            'activity_data' => $this->generateActivityData(SuspiciousActivity::TYPE_RAPID_INVESTMENTS),
            'detection_rules' => $this->generateDetectionRules(SuspiciousActivity::TYPE_RAPID_INVESTMENTS),
        ]);
    }

    public function unusualWithdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL,
            'activity_data' => $this->generateActivityData(SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL),
            'detection_rules' => $this->generateDetectionRules(SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL),
        ]);
    }

    private function generateActivityData(string $activityType): array
    {
        return match ($activityType) {
            SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT => [
                'fingerprint_hash' => $this->faker->sha256(),
                'matching_users' => [$this->faker->numberBetween(1, 100)],
                'device_info' => [
                    'screen' => '1920x1080',
                    'timezone' => 'Africa/Lusaka',
                ],
            ],
            SuspiciousActivity::TYPE_RAPID_INVESTMENTS => [
                'investment_count_24h' => $this->faker->numberBetween(5, 20),
                'current_amount' => $this->faker->randomFloat(2, 1000, 50000),
                'average_amount' => $this->faker->randomFloat(2, 500, 5000),
            ],
            SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL => [
                'withdrawal_count_24h' => $this->faker->numberBetween(3, 10),
                'current_amount' => $this->faker->randomFloat(2, 1000, 20000),
                'total_balance' => $this->faker->randomFloat(2, 5000, 50000),
            ],
            SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN => [
                'unique_ips_30_days' => $this->faker->numberBetween(5, 20),
                'failed_attempts' => $this->faker->numberBetween(5, 15),
                'current_ip' => $this->faker->ipv4(),
            ],
            default => [
                'generic_data' => $this->faker->words(3, true),
                'timestamp' => now()->toISOString(),
            ],
        };
    }

    private function generateDetectionRules(string $activityType): array
    {
        return match ($activityType) {
            SuspiciousActivity::TYPE_DUPLICATE_ACCOUNT => [
                'duplicate_device_fingerprint',
                'multiple_accounts_same_ip',
            ],
            SuspiciousActivity::TYPE_RAPID_INVESTMENTS => [
                'rapid_investment_frequency',
                'unusual_investment_amount',
            ],
            SuspiciousActivity::TYPE_UNUSUAL_WITHDRAWAL => [
                'rapid_withdrawal_attempts',
                'early_full_withdrawal',
            ],
            SuspiciousActivity::TYPE_SUSPICIOUS_LOGIN => [
                'multiple_ip_addresses',
                'excessive_failed_logins',
            ],
            default => [
                'generic_rule',
            ],
        };
    }
}