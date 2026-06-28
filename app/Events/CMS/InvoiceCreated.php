<?php

namespace App\Events\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public string $source // 'growbuilder', 'growmarket', 'manual'
    ) {}
}
