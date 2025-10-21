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
            // Award initial points to new member (100 LP as per documentation)
            $this->pointService->awardPoints(
                user: $event->user,
                source: 'registration',
                lpAmount: 100,
                mapAmount: 0, // No MAP on registration, only LP
                description: "Welcome to MyGrowNet! Initial registration bonus",
                reference: $event->user
            );

            Log::info('Registration points awarded', [
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award registration points', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }
}
