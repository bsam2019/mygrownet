<?php

namespace App\Domain\Core\Services;

class EventOwnershipRegistry
{
    private array $events = [];

    public function register(string $eventName, string $owner): void
    {
        if (isset($this->events[$eventName]) && $this->events[$eventName] !== $owner) {
            throw new \RuntimeException(
                "Event '{$eventName}' is already registered to '{$this->events[$eventName]}', cannot register to '{$owner}'"
            );
        }

        $this->events[$eventName] = $owner;
    }

    public function canPublish(string $eventName): bool
    {
        return isset($this->events[$eventName]);
    }

    public function owner(string $eventName): ?string
    {
        return $this->events[$eventName] ?? null;
    }

    public function all(): array
    {
        return $this->events;
    }

    public function eventsOwnedBy(string $owner): array
    {
        return array_keys(array_filter($this->events, fn($o) => $o === $owner));
    }
}
