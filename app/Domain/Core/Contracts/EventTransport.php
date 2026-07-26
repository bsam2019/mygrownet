<?php

namespace App\Domain\Core\Contracts;

interface EventTransport
{
    public function dispatch(string $eventName, array $payload, array $context = []): void;

    public function subscribe(string $eventName, callable|array $handler): void;

    public function supports(string $eventName): bool;
}
