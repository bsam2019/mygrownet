<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\EventTransport;
use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class EventDispatcher
{
    private static array $publishedInRequest = [];

    public function __construct(
        private readonly EventOwnershipRegistry $registry,
        private readonly ?EventTransport $transport = null,
    ) {}

    public function dispatch(string $eventName, array $payload, ?string $causationId = null): PlatformEvent
    {
        $context = $this->resolveContext();

        if (!$this->registry->canPublish($eventName)) {
            $this->logOwnershipViolation($eventName);
            throw new \RuntimeException("Event '{$eventName}' is not registered for publishing");
        }

        $platformEvent = new PlatformEvent(
            eventId: (string) Str::uuid(),
            eventName: $eventName,
            eventVersion: '1.0',
            publisher: $context->applicationId ?: 'platform',
            occurredAt: new \DateTimeImmutable(),
            correlationId: $this->getCorrelationId($causationId),
            causationId: $causationId,
            context: $context,
            payload: $payload,
        );

        self::$publishedInRequest[] = $platformEvent;

        Event::dispatch($eventName, $platformEvent);

        if ($this->transport && $this->transport->supports($eventName)) {
            $this->transport->dispatch($eventName, $payload, [
                'correlation_id' => $platformEvent->correlationId,
                'publisher' => $platformEvent->publisher,
                'occurred_at' => $platformEvent->occurredAt->format('c'),
            ]);
        }

        return $platformEvent;
    }

    public function dispatchAndForget(string $eventName, array $payload, ?string $causationId = null): void
    {
        $this->dispatch($eventName, $payload, $causationId);
    }

    private function getCorrelationId(?string $causationId): string
    {
        if ($causationId) {
            $parent = collect(self::$publishedInRequest)
                ->firstWhere('eventId', $causationId);
            return $parent ? $parent->correlationId : $causationId;
        }

        return (string) Str::uuid();
    }

    private function logOwnershipViolation(string $eventName): void
    {
        logger()->warning('Event ownership violation', [
            'event_name' => $eventName,
            'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['class'] ?? 'unknown',
            'trace_id' => app()->has(PlatformContext::class)
                ? app(PlatformContext::class)->traceId
                : null,
        ]);
    }

    private function resolveContext(): PlatformContext
    {
        if (app()->has(PlatformContext::class)) {
            return app(PlatformContext::class);
        }

        return PlatformContext::make(
            userId: '',
            organizationId: '',
            applicationId: 'platform',
        );
    }

    public static function clearPublished(): void
    {
        self::$publishedInRequest = [];
    }
}
