<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Platform\Contracts\ServiceRegistry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiGateway
{
    private array $localRoutes = [];

    public function __construct(
        private IntegrationRegistry $registry,
        private ServiceRegistry $serviceRegistry,
        private ?ContractInvoker $invoker = null,
    ) {}

    public function registerLocal(string $contractClass, string $serviceId): void
    {
        $this->localRoutes[$contractClass] = $serviceId;
    }

    public function call(string $contractClass, string $method, array $args = [], ?callable $fallback = null): mixed
    {
        // Try local resolution first
        try {
            if ($this->invoker) {
                return $this->invoker->call($contractClass, $method, $args, $fallback);
            }

            $provider = $this->registry->resolve($contractClass);
            return $provider->{$method}(...$args);
        } catch (\Throwable $e) {
            Log::info("ApiGateway: local call failed for {$contractClass}::{$method}, trying remote", [
                'error' => $e->getMessage(),
            ]);
        }

        // Fall back to remote resolution via service registry
        $serviceId = $this->localRoutes[$contractClass] ?? null;
        if (!$serviceId) {
            if ($fallback) return $fallback();
            throw new \RuntimeException("No route registered for {$contractClass}");
        }

        $endpoint = $this->serviceRegistry->resolve($serviceId);
        if (!$endpoint || !$endpoint['healthy']) {
            if ($fallback) return $fallback();
            throw new \RuntimeException("Service {$serviceId} is unavailable");
        }

        return $this->callRemote($endpoint['url'], $method, $args);
    }

    private function callRemote(string $baseUrl, string $method, array $args): mixed
    {
        try {
            $response = Http::timeout(30)->post("{$baseUrl}/api/contract/{$method}", $args);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \RuntimeException("Remote call failed: {$response->status()} {$response->body()}");
        } catch (\Throwable $e) {
            Log::error("ApiGateway: remote call failed", [
                'url' => $baseUrl,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getLocalRoutes(): array
    {
        return $this->localRoutes;
    }
}
