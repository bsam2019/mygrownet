<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\Models\EventOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class OutboxService
{
    public function __construct(
        private MetricsService $metrics,
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    public function insert(string $eventName, array $payload, array $context, string $publisher): EventOutbox
    {
        return EventOutbox::create([
            'event_name' => $eventName,
            'payload' => $payload,
            'context' => $context,
            'publisher' => $publisher,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => now(),
        ]);
    }

    public function publishPending(int $batchSize = 50): array
    {
        $results = ['published' => 0, 'failed' => 0];

        $pending = EventOutbox::where('status', 'pending')
            ->orderBy('created_at')
            ->limit($batchSize)
            ->get();

        foreach ($pending as $outbox) {
            try {
                Event::dispatch($outbox->event_name, $outbox->payload);

                $outbox->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                $results['published']++;
                $this->metrics->recordEventPublished($outbox->event_name);
                $this->eventDispatcher->dispatch('platform.outbox.event_published.v1', [
                    'event_name' => $outbox->event_name,
                    'outbox_id' => $outbox->id,
                ]);
            } catch (\Throwable $e) {
                $outbox->increment('attempts');

                if ($outbox->attempts >= 3) {
                    $outbox->update(['status' => 'failed']);
                }

                Log::error("Outbox: failed to publish '{$outbox->event_name}'", [
                    'id' => $outbox->id,
                    'error' => $e->getMessage(),
                ]);

                $results['failed']++;
                $this->metrics->recordEventFailed($outbox->event_name, get_class($e));
                $this->eventDispatcher->dispatch('platform.outbox.event_failed.v1', [
                    'event_name' => $outbox->event_name,
                    'outbox_id' => $outbox->id,
                    'error_message' => $e->getMessage(),
                    'attempts' => $outbox->attempts,
                ]);
            }
        }

        return $results;
    }

    public function archive(int $olderThanDays = 7): int
    {
        return EventOutbox::where('status', 'published')
            ->where('published_at', '<', now()->subDays($olderThanDays))
            ->delete();
    }

    public function pendingCount(): int
    {
        return EventOutbox::where('status', 'pending')->count();
    }

    public function failedCount(): int
    {
        return EventOutbox::where('status', 'failed')->count();
    }

    public function replayFailed(): array
    {
        $results = ['published' => 0, 'failed' => 0];

        $failed = EventOutbox::where('status', 'failed')
            ->orderBy('created_at')
            ->get();

        foreach ($failed as $outbox) {
            try {
                Event::dispatch($outbox->event_name, $outbox->payload);

                $outbox->update([
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                $results['published']++;
            } catch (\Throwable $e) {
                $outbox->increment('attempts');
                $results['failed']++;
            }
        }

        return $results;
    }
}
