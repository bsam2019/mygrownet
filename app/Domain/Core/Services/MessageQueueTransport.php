<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\EventTransport;
use App\Domain\Core\Jobs\DispatchEventJob;

class MessageQueueTransport implements EventTransport
{
    private array $subscribers = [];
    private array $supportedEvents = [];
    private string $connection;
    private string $queue;

    public function __construct(
        string $connection = 'default',
        string $queue = 'events',
    ) {
        $this->connection = $connection;
        $this->queue = $queue;
    }

    public function dispatch(string $eventName, array $payload, array $context = []): void
    {
        DispatchEventJob::dispatch($eventName, $payload, $context)
            ->onConnection($this->connection)
            ->onQueue($this->queue);
    }

    public function subscribe(string $eventName, callable|array $handler): void
    {
        $this->subscribers[$eventName][] = $handler;
        $this->supportedEvents[$eventName] = true;
    }

    public function supports(string $eventName): bool
    {
        return isset($this->supportedEvents[$eventName]);
    }

    public function getSubscribers(): array
    {
        return $this->subscribers;
    }

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }
}
