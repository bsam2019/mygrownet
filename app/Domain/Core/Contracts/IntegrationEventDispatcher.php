<?php

namespace App\Domain\Core\Contracts;

use App\Domain\Core\Events\PlatformEvent;

interface IntegrationEventDispatcher
{
    public function dispatch(PlatformEvent $event): void;
}
