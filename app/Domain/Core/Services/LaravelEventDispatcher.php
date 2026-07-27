<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Events\PlatformEvent;
use Illuminate\Support\Facades\Event;

class LaravelEventDispatcher implements IntegrationEventDispatcher
{
    public function dispatch(PlatformEvent $event): void
    {
        Event::dispatch($event);
    }
}
