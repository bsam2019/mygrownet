<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\ValueObjects\PlatformContext;

class PlatformEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $eventName,
        public readonly string $eventVersion,
        public readonly string $publisher,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly string $correlationId,
        public readonly ?string $causationId,
        public readonly PlatformContext $context,
        public readonly array $payload,
    ) {}

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName,
            'event_version' => $this->eventVersion,
            'publisher' => $this->publisher,
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::ATOM),
            'correlation_id' => $this->correlationId,
            'causation_id' => $this->causationId,
            'context' => $this->context->toArray(),
            'payload' => $this->payload,
        ];
    }
}
