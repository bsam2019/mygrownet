<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardReferralPoints implements ShouldQueue
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle the event when a user successfully refers someone
     */
    public function handle($event): void
    {
        try {
            // Award points to the referrer
            $this->pointService->awardPoints(
                user: $event->referrer,
                source: 'direct_referral',
                lpAmount: 150,
                mapAmount: 150,
                description: "Referred {$event->referee->name}",
                reference: $event->referee
            );

            Log::info('Referral points awarded', [
                'referrer_id' => $event->referrer->id,
                'referee_id' => $event->referee->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award referral points', [
                'error' => $e->getMessage(),
                'referrer_id' => $event->referrer->id ?? null,
            ]);
        }
    }
}
