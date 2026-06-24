<?php

namespace App\Events\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentureFundingCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VentureModel $venture,
    ) {}
}
