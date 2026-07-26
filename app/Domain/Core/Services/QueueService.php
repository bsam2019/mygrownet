<?php

namespace App\Domain\Core\Services;

use Illuminate\Support\Facades\Config;

class QueueService
{
    public function queueForApplication(string $applicationId): string
    {
        $queues = Config::get('platform.queue.per_application', []);

        return $queues[$applicationId] ?? Config::get('platform.queue.default_queue', 'default');
    }

    public function listenerTimeout(): int
    {
        return (int) Config::get('platform.queue.listener_timeout', 60);
    }

    public function maxRetries(): int
    {
        return (int) Config::get('platform.queue.retry_attempts', 3);
    }

    public function dlqRetentionDays(): int
    {
        return (int) Config::get('platform.queue.dlq_retention_days', 7);
    }

    public function allQueues(): array
    {
        return array_values(
            Config::get('platform.queue.per_application', []) + ['default' => 'default']
        );
    }

    public function applicationForQueue(string $queueName): ?string
    {
        $queues = Config::get('platform.queue.per_application', []);
        $flipped = array_flip($queues);
        return $flipped[$queueName] ?? null;
    }
}
