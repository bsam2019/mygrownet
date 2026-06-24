<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureInvestmentConfirmed;
use App\Notifications\VentureBuilder\InvestmentConfirmedNotification;

class SendInvestmentConfirmationNotification
{
    public function handle(VentureInvestmentConfirmed $event): void
    {
        $investment = $event->investment;
        $user = $investment->user;

        $user->notify(new InvestmentConfirmedNotification($investment));
    }
}
