<?php

namespace App\Events\CMS;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $productId,
        public int $companyId,
        public int $newQuantity,
        public string $reason = 'manual'
    ) {}
}
