<?php

namespace App\Events\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentureInvestmentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VentureInvestmentModel $investment,
    ) {}
}
