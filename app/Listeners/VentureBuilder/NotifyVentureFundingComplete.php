<?php

namespace App\Listeners\VentureBuilder;

use App\Events\VentureBuilder\VentureFundingCompleted;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Notifications\VentureBuilder\VentureFundedNotification;

class NotifyVentureFundingComplete
{
    public function handle(VentureFundingCompleted $event): void
    {
        $investments = VentureInvestmentModel::where('venture_id', $event->venture->id)
            ->confirmed()
            ->with('user')
            ->get();

        foreach ($investments as $investment) {
            $investment->user->notify(new VentureFundedNotification($event->venture));
        }
    }
}
