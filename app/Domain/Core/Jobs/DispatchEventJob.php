<?php

namespace App\Domain\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class DispatchEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $eventName,
        public array $payload,
        public array $context = [],
    ) {}

    public int $timeout = 60;
    public int $tries = 3;

    public function handle(): void
    {
        Event::dispatch($this->eventName, $this->payload);
    }

    public function failed(\Throwable $e): void
    {
        logger()->error('Event dispatch job failed', [
            'event_name' => $this->eventName,
            'error' => $e->getMessage(),
        ]);
    }
}
