<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureInvestmentConfirmed;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Notifications\VentureBuilder\InvestmentConfirmedNotification;

class SendInvestmentConfirmationNotification
{
    public function handle(VentureInvestmentConfirmed $event): void
    {
        $investment = VentureInvestmentModel::with('venture')->find($event->investmentId);
        if (!$investment) {
            return;
        }

        $user = $investment->user;
        $user->notify(new InvestmentConfirmedNotification($investment));
    }
}
