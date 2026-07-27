<?php

namespace App\Domain\PlatformBilling\Events;

use App\Domain\Core\Events\PlatformEvent;

class GracePeriodExpiring extends PlatformEvent
{
    public const NAME = 'platform.billing.grace_period.expiring.v1';

    public function __construct(
        string $eventId,
        string $eventVersion,
        string $publisher,
        \DateTimeImmutable $occurredAt,
        string $correlationId,
        ?string $causationId,
        \App\Domain\Core\ValueObjects\PlatformContext $context,
        array $payload,
    ) {
        parent::__construct(
            eventId: $eventId,
            eventName: self::NAME,
            eventVersion: $eventVersion,
            publisher: $publisher,
            occurredAt: $occurredAt,
            correlationId: $correlationId,
            causationId: $causationId,
            context: $context,
            payload: $payload,
        );
    }
}
