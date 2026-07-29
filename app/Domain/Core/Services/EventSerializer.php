<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Events\GenericPlatformEvent;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;

class EventSerializer
{
    public function serialize(PlatformEvent $event): string
    {
        return json_encode([
            'event_id' => $event->eventId,
            'event_name' => $event->eventName(),
            'publisher' => $event->publisher,
            'occurred_at' => $event->occurredAt->format(\DateTimeInterface::ATOM),
            'correlation_id' => $event->correlationId,
            'causation_id' => $event->causationId,
            'context' => $event->context->toArray(),
            'payload' => $event->payload,
            'event_version' => $event->eventVersion,
        ], JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $payload): PlatformEvent
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        $context = PlatformContext::fromArray($data['context'] ?? []);

        return new GenericPlatformEvent(
            eventId: $data['event_id'] ?? uniqid(),
            eventName: $data['event_name'] ?? 'unknown',
            eventVersion: $data['event_version'] ?? '1.0',
            publisher: $data['publisher'] ?? 'unknown',
            occurredAt: new \DateTimeImmutable($data['occurred_at'] ?? 'now'),
            correlationId: $data['correlation_id'] ?? uniqid(),
            causationId: $data['causation_id'] ?? null,
            context: $context,
            payload: $data['payload'] ?? [],
        );
    }

    public function headers(PlatformEvent $event): array
    {
        return [
            'X-Event-Id' => $event->eventId,
            'X-Event-Name' => $event->eventName(),
            'X-Publisher' => $event->publisher,
            'X-Occurred-At' => $event->occurredAt->format(\DateTimeInterface::ATOM),
            'X-Correlation-Id' => $event->correlationId,
            'X-Causation-Id' => $event->causationId,
            'Content-Type' => 'application/json',
        ];
    }
}
