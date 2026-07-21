<?php

namespace App\Events\BMS;

use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
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
