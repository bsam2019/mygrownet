<?php

namespace App\Events\BMS;

use App\Infrastructure\Persistence\Eloquent\BMS\ProductModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductSynced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductModel $product,
        public string $target // 'growbuilder', 'growmarket', 'both'
    ) {}
}
