<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureDividendPaid;
use App\Notifications\VentureBuilder\DividendPaidNotification;

class SendDividendPaymentNotification
{
    public function handle(VentureDividendPaid $event): void
    {
        $dividend = $event->dividend;
        $shareholder = $dividend->shareholder;
        $user = $shareholder->user;

        $user->notify(new DividendPaidNotification($dividend));
    }
}
