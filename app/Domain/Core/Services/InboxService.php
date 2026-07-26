<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\EventInbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboxService
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    public function alreadyProcessed(string $eventId): bool
    {
        return EventInbox::where('event_id', $eventId)
            ->whereIn('status', ['processed', 'processing'])
            ->exists();
    }

    public function markProcessing(string $eventId, string $eventName, array $payload, string $publisher): EventInbox
    {
        return EventInbox::create([
            'event_id' => $eventId,
            'event_name' => $eventName,
            'payload' => $payload,
            'publisher' => $publisher,
            'status' => 'processing',
            'received_at' => now(),
        ]);
    }

    public function markProcessed(string $eventId): void
    {
        EventInbox::where('event_id', $eventId)
            ->update(['status' => 'processed', 'processed_at' => now()]);
    }

    public function markFailed(string $eventId): void
    {
        EventInbox::where('event_id', $eventId)
            ->update(['status' => 'failed']);
    }

    public function processIfNew(string $eventId, string $eventName, array $payload, string $publisher, callable $handler): mixed
    {
        if ($this->alreadyProcessed($eventId)) {
            Log::debug("Inbox: skipping already-processed event {$eventId}");
            $this->eventDispatcher->dispatch('platform.inbox.event_duplicate.v1', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'publisher' => $publisher,
            ]);
            return null;
        }

        $inbox = $this->markProcessing($eventId, $eventName, $payload, $publisher);

        try {
            $result = $handler($payload);

            $this->markProcessed($eventId);
            $this->eventDispatcher->dispatch('platform.inbox.event_processed.v1', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'publisher' => $publisher,
            ]);
            return $result;
        } catch (\Throwable $e) {
            $this->markFailed($eventId);

            Log::warning("Inbox: event {$eventId} processing failed", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function stats(): array
    {
        $counts = EventInbox::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'received' => $counts['processing'] ?? 0,
            'processed' => $counts['processed'] ?? 0,
            'failed' => $counts['failed'] ?? 0,
            'total' => array_sum($counts),
        ];
    }
}
