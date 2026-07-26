<?php

namespace App\Domain\Core\Listeners;

use App\Domain\Core\Services\InboxService;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Facades\Log;

trait InboxAware
{
    protected function processWithInbox(
        string $eventId,
        string $eventName,
        array $payload,
        string $publisher,
        callable $handler,
    ): mixed {
        $inbox = app(InboxService::class);

        $traceId = app()->has(PlatformContext::class)
            ? app(PlatformContext::class)->traceId
            : null;

        Log::debug("InboxAware: processing event {$eventName} ({$eventId})", [
            'trace_id' => $traceId,
        ]);

        return $inbox->processIfNew($eventId, $eventName, $payload, $publisher, $handler);
    }
}
