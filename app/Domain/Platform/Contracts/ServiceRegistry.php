<?php

namespace App\Domain\Platform\Contracts;

interface ServiceRegistry
{
    public function register(string $serviceId, string $name, string $url, array $capabilities = [], array $metadata = []): void;

    public function unregister(string $serviceId): void;

    public function resolve(string $serviceId): ?array;

    public function findByCapability(string $capability): array;

    public function getAvailable(): array;

    public function markUnhealthy(string $serviceId): void;

    public function markHealthy(string $serviceId): void;
}
