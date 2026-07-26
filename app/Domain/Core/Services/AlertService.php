<?php

namespace App\Domain\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AlertService
{
    private const CACHE_PREFIX = 'alerts.';

    public function __construct(
        private MetricsService $metrics,
        private DeadLetterService $deadLetter,
    ) {}

    public function checkAll(): array
    {
        $alerts = [];
        $alerts[] = $this->checkFailureRate();
        $alerts[] = $this->checkDeadLetterQueue();
        $alerts[] = $this->checkQueueBacklog();
        return array_filter($alerts);
    }

    public function checkFailureRate(): ?array
    {
        $dashboard = $this->metrics->dashboard();
        $failureRate = $dashboard['events']['failure_rate'];
        $threshold = 5.0;

        if ($failureRate > $threshold) {
            $alert = [
                'type' => 'failure_rate_exceeded',
                'severity' => $failureRate > 10 ? 'critical' : 'warning',
                'value' => $failureRate,
                'threshold' => $threshold,
                'message' => "Event failure rate is {$failureRate}% (threshold: {$threshold}%)",
                'timestamp' => now()->toIso8601String(),
            ];
            $this->fire($alert);
            return $alert;
        }

        return null;
    }

    public function checkDeadLetterQueue(): ?array
    {
        $pending = $this->deadLetter->pending();

        if (count($pending) > 0) {
            $alert = [
                'type' => 'dead_letter_queue_not_empty',
                'severity' => count($pending) > 10 ? 'critical' : 'warning',
                'value' => count($pending),
                'threshold' => 0,
                'message' => "Dead letter queue has " . count($pending) . " pending events",
                'timestamp' => now()->toIso8601String(),
            ];
            $this->fire($alert);
            return $alert;
        }

        return null;
    }

    public function checkQueueBacklog(): ?array
    {
        $threshold = 1000;

        try {
            $size = \Illuminate\Support\Facades\Queue::size();
            if ($size > $threshold) {
                $alert = [
                    'type' => 'queue_backlog_exceeded',
                    'severity' => $size > 5000 ? 'critical' : 'warning',
                    'value' => $size,
                    'threshold' => $threshold,
                    'message' => "Queue backlog is {$size} jobs (threshold: {$threshold})",
                    'timestamp' => now()->toIso8601String(),
                ];
                $this->fire($alert);
                return $alert;
            }
        } catch (\Throwable $e) {
            Log::warning('AlertService: queue size check failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function checkListenerOffline(string $applicationId, int $minutesSinceLastHeartbeat): ?array
    {
        $threshold = 5;

        if ($minutesSinceLastHeartbeat > $threshold) {
            $alert = [
                'type' => 'listener_offline',
                'severity' => 'critical',
                'application' => $applicationId,
                'value' => $minutesSinceLastHeartbeat,
                'threshold' => $threshold,
                'message' => "Listener for '{$applicationId}' offline for {$minutesSinceLastHeartbeat} minutes",
                'timestamp' => now()->toIso8601String(),
            ];
            $this->fire($alert);
            return $alert;
        }

        return null;
    }

    private function fire(array $alert): void
    {
        $cacheKey = self::CACHE_PREFIX . $alert['type'];
        $lastFired = Cache::get($cacheKey);

        if ($lastFired && $lastFired > now()->subMinutes(15)->timestamp) {
            return;
        }

        Cache::put($cacheKey, now()->timestamp, now()->addHour());

        Log::warning('ALERT: ' . $alert['message'], $alert);

        // TODO: Send to notification channels (email, Slack, etc.) when configured
    }

    public function recent(): array
    {
        $keys = Cache::get(self::CACHE_PREFIX . 'keys', []);
        $alerts = [];
        foreach ($keys as $key) {
            $alerts[] = Cache::get($key);
        }
        return $alerts;
    }
}
