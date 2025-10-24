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
            $user = $event->user;

            // Award initial Lifetime Points to new member
            // 25 LP (Lifetime Points) - for level progression
            // No cash or BP on registration - those come from referral commissions
            $this->pointService->awardPoints(
                user: $user,
                source: 'registration',
                lpAmount: 25,
                mapAmount: 0, // No BP on registration
                description: "Welcome to MyGrowNet! Registration bonus: 25 LP",
                reference: $user
            );

            Log::info('Registration points awarded', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'lp_awarded' => 25,
                'bp_awarded' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award registration points', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }
}
