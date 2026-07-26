<?php

namespace MyGrowNet\Platform\Sdk\Events;

use MyGrowNet\Platform\Sdk\Context\PlatformContext;

class EventDispatcher
{
    public function __construct(
        private \App\Domain\Core\Services\EventDispatcher $core,
    ) {}

    public function dispatch(string $eventName, array $payload, ?string $causationId = null): PlatformEvent
    {
        $coreEvent = $this->core->dispatch($eventName, $payload, $causationId);
        return PlatformEvent::fromCore($coreEvent);
    }

    public function dispatchAndForget(string $eventName, array $payload, ?string $causationId = null): void
    {
        $this->core->dispatchAndForget($eventName, $payload, $causationId);
    }

    public static function instance(): self
    {
        return new self(app(\App\Domain\Core\Services\EventDispatcher::class));
    }
}
