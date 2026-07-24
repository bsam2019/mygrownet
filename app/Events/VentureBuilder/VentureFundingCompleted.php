<?php

namespace App\Events\VentureBuilder;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VentureFundingCompleted
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $ventureId,
        public string $ventureTitle,
        public float $totalRaised,
        public float $fundingTarget,
    ) {}
}
