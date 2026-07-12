<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureStatusChanged;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Notifications\VentureBuilder\VentureStatusNotification;

class SendVentureStatusNotification
{
    public function handle(VentureStatusChanged $event): void
    {
        $investments = VentureInvestmentModel::where('venture_id', $event->venture->id)
            ->confirmed()
            ->with('user')
            ->get();

        foreach ($investments as $investment) {
            $investment->user->notify(new VentureStatusNotification(
                $event->venture,
                $event->oldStatus,
                $event->newStatus,
            ));
        }
    }
}
