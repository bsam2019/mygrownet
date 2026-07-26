<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\DeadLetterEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetricsService
{
    private const CACHE_TTL = 60;

    public function __construct(
        private DeadLetterService $deadLetter,
        private QueueService $queueService,
    ) {}

    public function recordEventPublished(string $eventName): void
    {
        $this->incrementCounter("metrics.events.published.{$eventName}");
        $this->incrementCounter('metrics.events.published.total');
    }

    public function recordEventFailed(string $eventName, string $errorClass): void
    {
        $this->incrementCounter("metrics.events.failed.{$eventName}");
        $this->incrementCounter('metrics.events.failed.total');
        $this->incrementCounter("metrics.events.failed_by_class.{$errorClass}");
    }

    public function recordContractCall(string $contractClass, string $method, bool $success, float $durationMs): void
    {
        $key = "metrics.contracts.{$contractClass}.{$method}";

        $this->incrementCounter('metrics.contracts.total_calls');
        if ($success) {
            $this->incrementCounter('metrics.contracts.total_successes');
            $this->incrementCounter("{$key}.success");
        } else {
            $this->incrementCounter("{$key}.failure");
        }

        $this->recordTiming($key, $durationMs);
    }

    public function dashboard(): array
    {
        return Cache::remember('metrics.dashboard', self::CACHE_TTL, function () {
            $eventsPublished = $this->getCounter('metrics.events.published.total');
            $eventsFailed = $this->getCounter('metrics.events.failed.total');
            $failureRate = $eventsPublished > 0
                ? round(($eventsFailed / $eventsPublished) * 100, 2)
                : 0;

            $dlqCounts = $this->deadLetter->countByStatus();

            $contractCalls = $this->getCounter('metrics.contracts.total_calls');
            $contractSuccesses = $this->getCounter('metrics.contracts.total_successes');
            $contractSuccessRate = $contractCalls > 0
                ? round(($contractSuccesses / $contractCalls) * 100, 2)
                : 100;

            return [
                'events_published' => $eventsPublished,
                'events_failed' => $eventsFailed,
                'failure_rate' => $failureRate,
                'contract_calls' => $contractCalls,
                'contract_success_rate' => $contractSuccessRate,
                'slowest_contracts' => $this->getSlowestContracts(),
                'dead_letter_queue' => [
                    'pending' => $dlqCounts['pending'] ?? 0,
                    'replayed' => $dlqCounts['replayed'] ?? 0,
                    'replaying' => $dlqCounts['replaying'] ?? 0,
                ],
                'timestamp' => now()->toIso8601String(),
            ];
        });
    }

    private function getSlowestContracts(): array
    {
        $keys = Cache::get('metrics.keys', []);
        $contractTimingKeys = array_filter($keys, fn ($k) => str_contains($k, 'timings'));

        $results = [];
        foreach ($contractTimingKeys as $key) {
            $timings = Cache::get($key, []);
            if (empty($timings)) {
                continue;
            }

            $avg = round(array_sum($timings) / count($timings), 1);
            $max = round(max($timings), 1);

            preg_match('/metrics\.contracts\.(.+?)\.(.+?)\.timings/', $key, $matches);
            if (count($matches) === 3) {
                $results[] = [
                    'contract' => $matches[1],
                    'method' => $matches[2],
                    'avg_duration_ms' => $avg,
                    'max_duration_ms' => $max,
                    'call_count' => count($timings),
                ];
            }
        }

        usort($results, fn ($a, $b) => $b['avg_duration_ms'] <=> $a['avg_duration_ms']);
        return array_slice($results, 0, 10);
    }

    public function reset(): void
    {
        $keys = Cache::get('metrics.keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('metrics.keys');
        Cache::forget('metrics.dashboard');
    }

    private function incrementCounter(string $key): void
    {
        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addDay());
        $this->trackKey($key);
    }

    private function getCounter(string $key): int
    {
        return (int) Cache::get($key, 0);
    }

    private function recordTiming(string $key, float $durationMs): void
    {
        $timings = Cache::get("{$key}.timings", []);
        $timings[] = $durationMs;
        $timings = array_slice($timings, -100);
        Cache::put("{$key}.timings", $timings, now()->addDay());
        $this->trackKey("{$key}.timings");
    }

    private function trackKey(string $key): void
    {
        $keys = Cache::get('metrics.keys', []);
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put('metrics.keys', $keys, now()->addDay());
        }
    }
}
