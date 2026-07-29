<?php

namespace App\Domain\Core\Events;

class GenericPlatformEvent extends PlatformEvent
{
    public const NAME = 'generic';

    private string $customEventName;

    public function __construct(
        ?string $eventId = null,
        ?string $eventVersion = null,
        ?string $publisher = null,
        ?\DateTimeImmutable $occurredAt = null,
        ?string $correlationId = null,
        ?string $causationId = null,
        ?\App\Domain\Core\ValueObjects\PlatformContext $context = null,
        array $payload = [],
        ?string $entityId = null,
        string $eventName = 'generic',
    ) {
        parent::__construct(
            eventId: $eventId,
            eventVersion: $eventVersion,
            publisher: $publisher,
            occurredAt: $occurredAt,
            correlationId: $correlationId,
            causationId: $causationId,
            context: $context,
            payload: $payload,
            entityId: $entityId,
        );
        $this->customEventName = $eventName;
    }

    public function toPayload(): array
    {
        return $this->payload;
    }

    public function eventName(): string
    {
        return $this->customEventName;
    }
}
