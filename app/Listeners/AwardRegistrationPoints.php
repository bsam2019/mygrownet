<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardRegistrationPoints implements ShouldQueue
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle the event when a new user registers
     */
    public function handle($event): void
    {
        try {
            // Award initial points to new member
            // 35 LP (Lifetime Points) + 25 BP (Bonus Points/Monthly Activity Points)
            // Value per point: K2 (Total value: K120)
            $this->pointService->awardPoints(
                user: $event->user,
                source: 'registration',
                lpAmount: 35,
                mapAmount: 25, // MAP (Monthly Activity Points) = BP (Bonus Points)
                description: "Welcome to MyGrowNet! Registration bonus: 35 LP + 25 BP (K120 value)",
                reference: $event->user
            );

            Log::info('Registration points awarded', [
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'lp_awarded' => 35,
                'bp_awarded' => 25,
                'total_value' => 'K120',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award registration points', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }
}
