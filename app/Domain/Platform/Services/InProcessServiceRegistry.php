<?php

namespace App\Domain\Platform\Services;

use App\Domain\Platform\Contracts\ServiceRegistry;
use Illuminate\Support\Facades\Log;

class InProcessServiceRegistry implements ServiceRegistry
{
    private array $services = [];

    public function register(string $serviceId, string $name, string $url, array $capabilities = [], array $metadata = []): void
    {
        $this->services[$serviceId] = [
            'id' => $serviceId,
            'name' => $name,
            'url' => $url,
            'capabilities' => $capabilities,
            'metadata' => $metadata,
            'healthy' => true,
            'registered_at' => now()->toIso8601String(),
            'last_heartbeat' => now()->toIso8601String(),
        ];
    }

    public function unregister(string $serviceId): void
    {
        unset($this->services[$serviceId]);
    }

    public function resolve(string $serviceId): ?array
    {
        return $this->services[$serviceId] ?? null;
    }

    public function findByCapability(string $capability): array
    {
        return array_values(array_filter($this->services, fn($s) => in_array($capability, $s['capabilities'])));
    }

    public function getAvailable(): array
    {
        return array_values(array_filter($this->services, fn($s) => $s['healthy']));
    }

    public function markUnhealthy(string $serviceId): void
    {
        if (isset($this->services[$serviceId])) {
            $this->services[$serviceId]['healthy'] = false;
            Log::warning("Service marked unhealthy: {$serviceId}");
        }
    }

    public function markHealthy(string $serviceId): void
    {
        if (isset($this->services[$serviceId])) {
            $this->services[$serviceId]['healthy'] = true;
            $this->services[$serviceId]['last_heartbeat'] = now()->toIso8601String();
        }
    }

    public function heartbeat(string $serviceId): void
    {
        if (isset($this->services[$serviceId])) {
            $this->services[$serviceId]['last_heartbeat'] = now()->toIso8601String();
            $this->services[$serviceId]['healthy'] = true;
        }
    }

    public function getStaleServices(int $maxAgeMinutes = 5): array
    {
        $cutoff = now()->subMinutes($maxAgeMinutes);
        return array_values(array_filter($this->services, function ($s) use ($cutoff) {
            return $s['last_heartbeat'] < $cutoff->toIso8601String();
        }));
    }
}
