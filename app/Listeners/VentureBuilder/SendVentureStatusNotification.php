<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureStatusChanged;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Notifications\VentureBuilder\VentureStatusNotification;

class SendVentureStatusNotification
{
    public function handle(VentureStatusChanged $event): void
    {
        $venture = VentureModel::find($event->ventureId);
        if (!$venture) {
            return;
        }

        $investments = VentureInvestmentModel::where('venture_id', $event->ventureId)
            ->confirmed()
            ->with('user')
            ->get();

        foreach ($investments as $investment) {
            $investment->user->notify(new VentureStatusNotification(
                $venture,
                $event->oldStatus,
                $event->newStatus,
            ));
        }
    }
}
