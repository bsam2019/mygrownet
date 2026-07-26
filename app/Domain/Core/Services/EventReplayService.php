<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\EventOutbox;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventReplayService
{
    public function __construct(
        private MetricsService $metrics,
    ) {}

    public function replay(?string $eventName = null, ?string $from = null, ?string $to = null): array
    {
        $query = EventOutbox::where('status', 'published');

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        if ($from) {
            $query->where('published_at', '>=', $from);
        }

        if ($to) {
            $query->where('published_at', '<=', $to);
        }

        $results = ['published' => 0, 'failed' => 0];
        $events = $query->orderBy('published_at')->get();

        foreach ($events as $outbox) {
            try {
                Event::dispatch($outbox->event_name, $outbox->payload);
                $results['published']++;
                $this->metrics->recordEventPublished($outbox->event_name . '.replayed');
            } catch (\Throwable $e) {
                Log::error("EventReplay: failed to replay '{$outbox->event_name}'", [
                    'id' => $outbox->id,
                    'error' => $e->getMessage(),
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    public function eventsInRange(?string $from = null, ?string $to = null, ?string $eventName = null, int $perPage = 50): array
    {
        $query = EventOutbox::query();

        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->toArray();
    }

    public function availableEventNames(): array
    {
        return EventOutbox::select('event_name')
            ->distinct()
            ->orderBy('event_name')
            ->pluck('event_name')
            ->toArray();
    }
}
