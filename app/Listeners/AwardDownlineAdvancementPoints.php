<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardDownlineAdvancementPoints implements ShouldQueue
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle the event when a downline member advances to a new level
     */
    public function handle($event): void
    {
        try {
            $advancedUser = $event->user;
            $referrer = $advancedUser->referrer;

            if (!$referrer) {
                return;
            }

            // Award points to the referrer for their downline's advancement
            $this->pointService->awardPoints(
                user: $referrer,
                source: 'downline_advancement',
                lpAmount: 50,
                mapAmount: 50,
                description: "{$advancedUser->name} advanced to {$event->newLevel}",
                reference: $advancedUser
            );

            Log::info('Downline advancement points awarded', [
                'referrer_id' => $referrer->id,
                'advanced_user_id' => $advancedUser->id,
                'new_level' => $event->newLevel,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award downline advancement points', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }
}
