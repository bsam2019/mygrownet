<?php

namespace App\Events\VentureBuilder;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class VentureDividendPaid
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $dividendId,
        public int $ventureId,
        public int $shareholderId,
        public int $userId,
        public float $amount,
        public string $paymentReference,
        public string $period,
    ) {}
}
