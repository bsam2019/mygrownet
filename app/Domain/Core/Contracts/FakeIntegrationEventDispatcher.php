<?php

namespace App\Domain\Core\Contracts;

use App\Domain\Core\Events\PlatformEvent;

class FakeIntegrationEventDispatcher implements IntegrationEventDispatcher
{
    public array $events = [];

    public function dispatch(PlatformEvent $event): void
    {
        $this->events[] = $event;
    }
}
