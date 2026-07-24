<?php

namespace App\Events\VentureBuilder;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VentureStatusChanged
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $ventureId,
        public string $oldStatus,
        public string $newStatus,
        public string $ventureTitle,
        public string $ventureSlug,
    ) {}
}
