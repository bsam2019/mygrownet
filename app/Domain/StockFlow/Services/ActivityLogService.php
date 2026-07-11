<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Events\DomainEvent;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaActivityLogModel;

/**
 * Records domain events as immutable activity log entries.
 * Called by the ActivityLogListener which subscribes to all events.
 */
class ActivityLogService
{
    public function record(DomainEvent $event, string $context, string $eventName, string $description, ?string $subjectType = null, ?int $subjectId = null, ?int $actorUserId = null, array $extraPayload = []): void
    {
        SaActivityLogModel::create([
            'sa_company_id' => $event->getCompanyId(),
            'actor_user_id' => $actorUserId,
            'context' => $context,
            'event_name' => $eventName,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'payload' => array_merge($extraPayload, ['event_class' => get_class($event)]),
            'occurred_at' => $event->occurredAt(),
        ]);
    }
}
