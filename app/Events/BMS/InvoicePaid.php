<?php

namespace App\Events\BMS;

use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public float $amountPaid,
        public string $paymentMethod,
    ) {}
}
