<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductSold
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public $sale // Can be any sale model
    ) {}
}
