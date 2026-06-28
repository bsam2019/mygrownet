<?php

namespace App\Events\CMS;

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
