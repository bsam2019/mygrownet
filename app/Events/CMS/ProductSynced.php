<?php

namespace App\Events\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\ProductModel;
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
