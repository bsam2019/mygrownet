<?php

namespace App\Events\BMS;

use App\Infrastructure\Persistence\Eloquent\CMS\PaymentModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PaymentModel $payment
    ) {}
}
