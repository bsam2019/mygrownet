<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\HealthService;
use App\Domain\Core\Enums\HealthStatus;
use App\Domain\Platform\Contracts\ServiceRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HealthServiceImpl implements HealthService
{
    private const CACHE_KEY = 'platform_health';
    private const CACHE_TTL = 60;

    public function __construct(
        private ModuleDiscovery $discovery,
        private readonly ServiceRegistry $serviceRegistry,
    ) {}

    public function check(?string $applicationId = null): HealthStatus
    {
        if ($applicationId) {
            return $this->checkApplication($applicationId);
        }

        return $this->checkPlatform();
    }

    public function isHealthy(?string $applicationId = null): bool
    {
        return $this->check($applicationId)->isOperational();
    }

    public function details(?string $applicationId = null): array
    {
        if ($applicationId) {
            return $this->appHealthDetails($applicationId);
        }

        return $this->platformHealthDetails();
    }

    public function all(): array
    {
        $results = [];
        foreach ($this->discovery->all() as $manifest) {
            $appId = $manifest['id'];
            $results[$appId] = $this->appHealthDetails($appId);
            $endpoint = $this->serviceRegistry->resolve($appId);
            if ($endpoint) {
                $results[$appId]['registry_healthy'] = $endpoint['healthy'] ?? false;
                $results[$appId]['last_heartbeat'] = $endpoint['last_heartbeat'] ?? null;
            }
        }
        return $results;
    }

    private function checkPlatform(): HealthStatus
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                DB::connection()->getPdo();
                if ($this->isQueueHealthy()) {
                    return HealthStatus::Healthy;
                }
                return HealthStatus::Degraded;
            } catch (\Throwable $e) {
                Log::error('Platform health check failed', ['error' => $e->getMessage()]);
                return HealthStatus::Unavailable;
            }
        });
    }

    private function checkApplication(string $applicationId): HealthStatus
    {
        $manifest = $this->discovery->find($applicationId);
        if (!$manifest) {
            return HealthStatus::Unavailable;
        }

        $details = $this->appHealthDetails($applicationId);
        return $details['status'];
    }

    private function platformHealthDetails(): array
    {
        $dbOk = false;
        $queueOk = false;
        $cacheOk = false;

        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable $e) {
            Log::error('Health check: database unavailable', ['error' => $e->getMessage()]);
        }

        $queueOk = $this->isQueueHealthy();

        try {
            Cache::store()->has('health_check_ping');
            $cacheOk = true;
        } catch (\Throwable $e) {
            Log::error('Health check: cache unavailable', ['error' => $e->getMessage()]);
        }

        $checks = ['database' => $dbOk, 'queue' => $queueOk, 'cache' => $cacheOk];
        $allOk = $dbOk && $queueOk && $cacheOk;
        $someOk = $dbOk || $queueOk || $cacheOk;

        return [
            'status' => $allOk ? HealthStatus::Healthy : ($someOk ? HealthStatus::Degraded : HealthStatus::Unavailable),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    private function appHealthDetails(string $applicationId): array
    {
        $manifest = $this->discovery->find($applicationId);
        $platform = $this->platformHealthDetails();

        $appStatus = HealthStatus::Healthy;

        if (!$manifest) {
            $appStatus = HealthStatus::Unavailable;
        } elseif (!$platform['status']->isOperational()) {
            $appStatus = HealthStatus::Degraded;
        }

        if ($manifest && isset($manifest['health_url'])) {
            $endpoint = $this->serviceRegistry->resolve($applicationId);
            if ($endpoint && $endpoint['url'] !== 'local') {
                try {
                    $response = Http::timeout(5)->get($manifest['health_url']);
                    $remoteHealthy = $response->successful();
                    if (!$remoteHealthy) {
                        $appStatus = HealthStatus::Degraded;
                    }
                    $checks['remote'] = $remoteHealthy;
                } catch (\Throwable $e) {
                    Log::warning("Health check: remote endpoint unavailable for {$applicationId}", [
                        'url' => $manifest['health_url'],
                        'error' => $e->getMessage(),
                    ]);
                    $checks['remote'] = false;
                    if ($endpoint && $endpoint['url'] !== 'local') {
                        $appStatus = HealthStatus::Degraded;
                    }
                }
            }
        }

        return [
            'status' => $appStatus,
            'application' => $applicationId,
            'timestamp' => now()->toIso8601String(),
            'platform' => $platform['checks'] ?? [],
            'checks' => $checks ?? [],
        ];
    }

    private function isQueueHealthy(): bool
    {
        try {
            $size = Queue::size();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
