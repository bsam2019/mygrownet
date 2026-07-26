<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\DeadLetterEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeadLetterService
{
    public function capture(string $eventName, array $payload, string $errorMessage, string $errorClass, ?string $queue = null): DeadLetterEvent
    {
        return DeadLetterEvent::create([
            'event_name' => $eventName,
            'payload' => $payload,
            'error_message' => $errorMessage,
            'error_class' => $errorClass,
            'queue' => $queue,
            'status' => 'pending',
            'attempts' => 0,
            'failed_at' => now(),
        ]);
    }

    public function replay(int $id): bool
    {
        $event = DeadLetterEvent::findOrFail($id);

        if ($event->status !== 'pending') {
            return false;
        }

        try {
            $event->update(['status' => 'replaying']);

            event($event->event_name, $event->payload);

            $event->update(['status' => 'replayed', 'attempts' => $event->attempts + 1]);
            Log::info("Dead letter event {$id} replayed successfully");
            return true;
        } catch (\Throwable $e) {
            $event->update([
                'status' => 'pending',
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'attempts' => $event->attempts + 1,
            ]);
            Log::warning("Dead letter event {$id} replay failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function replayAll(string $eventName = null): array
    {
        $query = DeadLetterEvent::where('status', 'pending');
        if ($eventName) {
            $query->where('event_name', $eventName);
        }

        $results = ['succeeded' => 0, 'failed' => 0];
        foreach ($query->cursor() as $event) {
            if ($this->replay($event->id)) {
                $results['succeeded']++;
            } else {
                $results['failed']++;
            }
        }
        return $results;
    }

    public function pending(): array
    {
        return DeadLetterEvent::where('status', 'pending')
            ->orderBy('failed_at', 'desc')
            ->get()
            ->toArray();
    }

    public function all(array $filters = []): array
    {
        $query = DeadLetterEvent::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['event_name'])) {
            $query->where('event_name', $filters['event_name']);
        }

        return $query->orderBy('failed_at', 'desc')
            ->paginate($filters['per_page'] ?? 50)
            ->toArray();
    }

    public function purgeOlderThan(int $days): int
    {
        return DeadLetterEvent::where('failed_at', '<', now()->subDays($days))
            ->delete();
    }

    public function countByStatus(): array
    {
        return DeadLetterEvent::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }
}
