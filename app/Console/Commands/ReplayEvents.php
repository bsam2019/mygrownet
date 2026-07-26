<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\EventReplayService;
use Illuminate\Console\Command;

class ReplayEvents extends Command
{
    protected $signature = 'platform:replay-events
        {--event= : Filter by event name}
        {--from= : Start date (Y-m-d)}
        {--to= : End date (Y-m-d)}';

    protected $description = 'Replay published events from the outbox';

    public function handle(EventReplayService $replay): int
    {
        $eventName = $this->option('event');
        $from = $this->option('from');
        $to = $this->option('to');

        $query = [];
        if ($eventName) {
            $query['event_name'] = $eventName;
        }
        if ($from) {
            $query['from'] = $from;
        }
        if ($to) {
            $query['to'] = $to;
        }

        $total = \App\Domain\Core\Models\EventOutbox::where('status', 'published')
            ->when($eventName, fn($q) => $q->where('event_name', $eventName))
            ->when($from, fn($q) => $q->where('published_at', '>=', $from))
            ->when($to, fn($q) => $q->where('published_at', '<=', $to))
            ->count();

        if ($total === 0) {
            $this->warn('No matching events found.');
            return 1;
        }

        if (!$this->confirm("Replay {$total} events?")) {
            $this->info('Cancelled.');
            return 0;
        }

        $results = $replay->replay($eventName, $from, $to);

        $this->info("Replayed: {$results['published']}, Failed: {$results['failed']}");

        return $results['failed'] > 0 ? 1 : 0;
    }
}
