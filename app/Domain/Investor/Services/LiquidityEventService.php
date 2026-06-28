<?php

namespace App\Domain\Investor\Services;

use App\Models\LiquidityEvent;
use App\Models\LiquidityEventParticipation;
use App\Models\InvestorAccount;
use Illuminate\Support\Collection;

/**
 * Liquidity Event Service
 * 
 * Manages company-initiated liquidity events like buybacks,
 * acquisitions, mergers, etc.
 */
class LiquidityEventService
{
    /**
     * Get active liquidity events
     */
    public function getActiveEvents(): Collection
    {
        return LiquidityEvent::active()
            ->orderBy('announcement_date', 'desc')
            ->get();
    }

    /**
     * Get event details with participation info
     */
    public function getEventWithParticipation(int $eventId, InvestorAccount $investor): array
    {
        $event = LiquidityEvent::findOrFail($eventId);
        $participation = LiquidityEventParticipation::where('liquidity_event_id', $eventId)
            ->where('investor_account_id', $investor->id)
            ->first();

        return [
            'event' => $event,
            'participation' => $participation,
            'can_participate' => $this->canParticipate($event, $investor),
        ];
    }

    /**
     * Check if investor can participate in event
     */
    public function canParticipate(LiquidityEvent $event, InvestorAccount $investor): bool
    {
        if (!$event->isRegistrationOpen()) {
            return false;
        }

        // Check eligibility criteria
        if ($event->eligibility_criteria) {
            $criteria = $event->eligibility_criteria;
            
            // Minimum shares requirement
            if (isset($criteria['min_shares']) && $investor->equity_percentage < $criteria['min_shares']) {
                return false;
            }

            // Minimum holding period
            if (isset($criteria['min_holding_months'])) {
                $holdingMonths = $investor->created_at->diffInMonths(now());
                if ($holdingMonths < $criteria['min_holding_months']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Register interest in a liquidity event
     */
    public function registerInterest(LiquidityEvent $event, InvestorAccount $investor): LiquidityEventParticipation
    {
        if (!$this->canParticipate($event, $investor)) {
            throw new \InvalidArgumentException('Investor is not eligible for this event.');
        }

        return LiquidityEventParticipation::updateOrCreate(
            [
                'liquidity_event_id' => $event->id,
                'investor_account_id' => $investor->id,
            ],
            [
                'status' => 'interested',
            ]
        );
    }

    /**
     * Register for participation with shares offered
     */
    public function registerParticipation(
        LiquidityEvent $event,
        InvestorAccount $investor,
        float $sharesOffered,
        ?array $bankDetails = null
    ): LiquidityEventParticipation {
        if (!$this->canParticipate($event, $investor)) {
            throw new \InvalidArgumentException('Investor is not eligible for this event.');
        }

        if ($sharesOffered > $investor->equity_percentage) {
            throw new \InvalidArgumentException('Cannot offer more shares than owned.');
        }

        $participation = LiquidityEventParticipation::updateOrCreate(
            [
                'liquidity_event_id' => $event->id,
                'investor_account_id' => $investor->id,
            ],
            [
                'status' => 'registered',
                'shares_offered' => $sharesOffered,
                'bank_details' => $bankDetails,
                'registered_at' => now(),
            ]
        );

        // Calculate estimated amount
        if ($event->price_per_share) {
            $participation->update([
                'amount_to_receive' => $sharesOffered * $event->price_per_share,
            ]);
        }

        return $participation;
    }

    /**
     * Withdraw from participation
     */
    public function withdrawParticipation(LiquidityEventParticipation $participation): void
    {
        if ($participation->status === 'completed') {
            throw new \InvalidArgumentException('Cannot withdraw from completed participation.');
        }

        $participation->update(['status' => 'withdrawn']);
    }

    /**
     * Get investor's participation history
     */
    public function getInvestorParticipations(InvestorAccount $investor): Collection
    {
        return LiquidityEventParticipation::where('investor_account_id', $investor->id)
            ->with('liquidityEvent')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Admin: Approve participation
     */
    public function approveParticipation(
        LiquidityEventParticipation $participation,
        float $sharesAccepted
    ): void {
        $event = $participation->liquidityEvent;
        
        $participation->update([
            'status' => 'approved',
            'shares_accepted' => $sharesAccepted,
            'amount_to_receive' => $sharesAccepted * ($event->price_per_share ?? 0),
        ]);
    }

    /**
     * Admin: Complete participation (payment made)
     */
    public function completeParticipation(
        LiquidityEventParticipation $participation,
        float $amountPaid
    ): void {
        $participation->update([
            'status' => 'completed',
            'amount_received' => $amountPaid,
            'completed_at' => now(),
        ]);

        // Update investor's equity
        $investor = $participation->investorAccount;
        $investor->decrement('equity_percentage', $participation->shares_accepted);
    }
}
