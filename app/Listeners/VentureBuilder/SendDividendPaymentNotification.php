<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureDividendPaid;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use App\Notifications\VentureBuilder\DividendPaidNotification;

class SendDividendPaymentNotification
{
    public function handle(VentureDividendPaid $event): void
    {
        $dividend = VentureDividendModel::with(['venture', 'shareholder.user'])->find($event->dividendId);
        if (!$dividend || !$dividend->shareholder || !$dividend->shareholder->user) {
            return;
        }

        $dividend->shareholder->user->notify(new DividendPaidNotification($dividend));
    }
}
