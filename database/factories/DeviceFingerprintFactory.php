<?php

namespace Database\Factories;

use App\Models\DeviceFingerprint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFingerprintFactory extends Factory
{
    protected $model = DeviceFingerprint::class;

    public function definition(): array
    {
        $deviceInfo = [
            'screen' => $this->faker->randomElement(['1920x1080', '1366x768', '1440x900', '1280x720']),
            'timezone' => $this->faker->randomElement(['Africa/Lusaka', 'UTC', 'Africa/Johannesburg']),
            'language' => $this->faker->randomElement(['en-US', 'en-GB', 'en-ZM']),
            'platform' => $this->faker->randomElement(['Win32', 'MacIntel', 'Linux x86_64']),
        ];

        $browserInfo = [
            'name' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'version' => $this->faker->randomElement(['91.0.4472.124', '89.0.4389.82', '14.1.1', '91.0.864.59']),
        ];

        $userAgent = $this->faker->userAgent();

        return [
            'user_id' => User::factory(),
            'fingerprint_hash' => DeviceFingerprint::generateFingerprint($deviceInfo, $browserInfo, $userAgent),
            'user_agent' => $userAgent,
            'ip_address' => $this->faker->ipv4(),
            'device_info' => $deviceInfo,
            'browser_info' => $browserInfo,
            'is_trusted' => $this->faker->boolean(80),
            'first_seen_at' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'last_seen_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ];
    }

    public function trusted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trusted' => true,
        ]);
    }

    public function untrusted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trusted' => false,
        ]);
    }

    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_seen_at' => now()->subHours($this->faker->numberBetween(1, 23)),
        ]);
    }
}