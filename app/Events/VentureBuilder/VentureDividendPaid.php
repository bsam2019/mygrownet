<?php

namespace App\Events\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentureDividendPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VentureDividendModel $dividend,
    ) {}
}
