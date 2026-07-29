<?php

namespace App\Domain\Core\Events;

use App\Domain\Core\ValueObjects\PlatformContext;

class PlatformEvent
{
    public const NAME = 'platform.event';

    public readonly string $eventId;
    public readonly string $eventName;
    public readonly string $eventVersion;
    public readonly string $publisher;
    public readonly \DateTimeImmutable $occurredAt;
    public readonly string $correlationId;
    public readonly ?string $causationId;
    public readonly PlatformContext $context;
    public readonly array $payload;
    public readonly ?string $entityId;

    public function __construct(
        string $eventId = '',
        string $eventName = '',
        string $eventVersion = '1.0',
        string $publisher = '',
        ?\DateTimeImmutable $occurredAt = null,
        string $correlationId = '',
        ?string $causationId = null,
        ?PlatformContext $context = null,
        array $payload = [],
        ?string $entityId = null,
    ) {
        $this->eventId = $eventId ?: \Illuminate\Support\Str::uuid()->toString();
        $this->eventName = $eventName;
        $this->eventVersion = $eventVersion;
        $this->publisher = $publisher ?: config('app.name', 'mygrownet');
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
        $this->correlationId = $correlationId ?: \Illuminate\Support\Str::uuid()->toString();
        $this->causationId = $causationId;
        $this->context = $context ?? PlatformContext::make(
            userId: '',
            organizationId: '',
            applicationId: 'system',
        );
        $this->payload = $payload;
        $this->entityId = $entityId;
    }

    public function eventName(): string
    {
        return $this->eventName ?: static::NAME;
    }

    public function toPayload(): array
    {
        return $this->payload;
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_name' => $this->eventName(),
            'event_version' => $this->eventVersion,
            'publisher' => $this->publisher,
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::ATOM),
            'correlation_id' => $this->correlationId,
            'causation_id' => $this->causationId,
            'context' => $this->context->toArray(),
            'payload' => $this->toPayload(),
            'entity_id' => $this->entityId,
        ];
    }
}
