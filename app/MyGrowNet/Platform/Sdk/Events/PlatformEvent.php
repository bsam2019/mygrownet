<?php

namespace MyGrowNet\Platform\Sdk\Events;

use MyGrowNet\Platform\Sdk\Context\PlatformContext;

class PlatformEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $eventName,
        public readonly string $publisher,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly string $correlationId,
        public readonly ?string $causationId,
        public readonly PlatformContext $context,
        public readonly array $payload,
    ) {}

    public static function fromCore(\App\Domain\Core\Events\PlatformEvent $core): self
    {
        return new self(
            eventId: $core->eventId,
            eventName: $core->eventName,
            publisher: $core->publisher,
            occurredAt: $core->occurredAt,
            correlationId: $core->correlationId,
            causationId: $core->causationId ?? null,
            context: PlatformContext::fromCore($core->context),
            payload: $core->payload,
        );
    }
}
