<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureFundingCompleted;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Notifications\VentureBuilder\VentureFundedNotification;

class NotifyVentureFundingComplete
{
    public function handle(VentureFundingCompleted $event): void
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
            $investment->user->notify(new VentureFundedNotification($venture));
        }
    }
}
