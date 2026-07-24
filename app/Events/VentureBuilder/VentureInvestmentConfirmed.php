<?php

namespace App\Events\VentureBuilder;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VentureInvestmentConfirmed
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $investmentId,
        public int $ventureId,
        public int $userId,
        public float $amount,
        public string $paymentReference,
        public string $ventureTitle,
    ) {}
}
