<?php

namespace App\Domain\Wedding\Services;

use App\Domain\Wedding\Entities\WeddingEvent;
use App\Domain\Wedding\Repositories\WeddingEventRepositoryInterface;
use App\Domain\Wedding\ValueObjects\WeddingBudget;
use Carbon\Carbon;

class WeddingPlanningService
{
    public function __construct(
        private WeddingEventRepositoryInterface $weddingEventRepository
    ) {}

    public function createWeddingEvent(
        int $userId,
        string $partnerName,
        Carbon $weddingDate,
        float $budgetAmount,
        int $guestCount = 0
    ): WeddingEvent {
        // Validate wedding date is in the future
        if ($weddingDate->isPast()) {
            throw new \InvalidArgumentException('Wedding date must be in the future');
        }

        // Check if user already has an active wedding event
        $existingEvent = $this->weddingEventRepository->findUserActiveEvent($userId);
        if ($existingEvent) {
            throw new \DomainException('User already has an active wedding event');
        }

        $budget = WeddingBudget::fromAmount($budgetAmount);
        $weddingEvent = WeddingEvent::create($userId, $partnerName, $weddingDate, $budget, $guestCount);

        return $this->weddingEventRepository->save($weddingEvent);
    }

    public function generateBudgetBreakdown(WeddingBudget $totalBudget): array
    {
        // Standard wedding budget allocation percentages
        $allocations = [
            'venue' => 40,      // Venue and catering
            'photography' => 15, // Photography and videography
            'decoration' => 10,  // Flowers and decoration
            'music' => 8,        // Music and entertainment
            'transport' => 5,    // Transportation
            'makeup' => 5,       // Beauty and makeup
            'attire' => 10,      // Wedding attire
            'miscellaneous' => 7 // Other expenses
        ];

        $breakdown = [];
        foreach ($allocations as $category => $percentage) {
            $breakdown[$category] = [
                'percentage' => $percentage,
                'amount' => $totalBudget->allocatePercentage($percentage)->getAmount(),
                'formatted' => $totalBudget->allocatePercentage($percentage)->getFormattedAmount()
            ];
        }

        return $breakdown;
    }

    public function generateWeddingTimeline(Carbon $weddingDate): array
    {
        $timeline = [];
        
        // 12 months before
        $timeline[] = [
            'period' => '12 months before',
            'date' => $weddingDate->copy()->subMonths(12),
            'tasks' => [
                'Set wedding date and budget',
                'Create guest list',
                'Book venue',
                'Hire wedding planner (if needed)'
            ]
        ];

        // 9 months before
        $timeline[] = [
            'period' => '9 months before',
            'date' => $weddingDate->copy()->subMonths(9),
            'tasks' => [
                'Book photographer',
                'Choose wedding theme and colors',
                'Order wedding dress',
                'Book catering services'
            ]
        ];

        // 6 months before
        $timeline[] = [
            'period' => '6 months before',
            'date' => $weddingDate->copy()->subMonths(6),
            'tasks' => [
                'Send save-the-dates',
                'Book music/entertainment',
                'Order wedding cake',
                'Plan honeymoon'
            ]
        ];

        // 3 months before
        $timeline[] = [
            'period' => '3 months before',
            'date' => $weddingDate->copy()->subMonths(3),
            'tasks' => [
                'Send wedding invitations',
                'Final dress fitting',
                'Book transportation',
                'Confirm all vendors'
            ]
        ];

        // 1 month before
        $timeline[] = [
            'period' => '1 month before',
            'date' => $weddingDate->copy()->subMonth(),
            'tasks' => [
                'Final headcount to caterer',
                'Confirm timeline with vendors',
                'Pick up wedding dress',
                'Prepare wedding favors'
            ]
        ];

        // 1 week before
        $timeline[] = [
            'period' => '1 week before',
            'date' => $weddingDate->copy()->subWeek(),
            'tasks' => [
                'Rehearsal dinner',
                'Final venue walkthrough',
                'Confirm all details',
                'Relax and prepare'
            ]
        ];

        return $timeline;
    }

    public function calculateRecommendedBudget(int $guestCount, string $weddingStyle = 'standard'): WeddingBudget
    {
        // Base cost per guest in Kwacha
        $costPerGuest = match ($weddingStyle) {
            'budget' => 800,     // K800 per guest
            'standard' => 1200,  // K1,200 per guest
            'premium' => 2000,   // K2,000 per guest
            'luxury' => 3500,    // K3,500 per guest
            default => 1200
        };

        $totalAmount = $guestCount * $costPerGuest;
        
        // Add base costs (venue, photography, etc.)
        $baseCosts = match ($weddingStyle) {
            'budget' => 15000,
            'standard' => 25000,
            'premium' => 50000,
            'luxury' => 100000,
            default => 25000
        };

        return WeddingBudget::fromAmount($totalAmount + $baseCosts);
    }

    public function getWeddingProgress(WeddingEvent $weddingEvent): array
    {
        $totalTasks = 20; // Total planning tasks
        $completedTasks = 0;

        // Check completion based on wedding event data
        if ($weddingEvent->getVenueName()) $completedTasks += 3;
        if ($weddingEvent->getBudget()->getAmount() > 0) $completedTasks += 2;
        if ($weddingEvent->getGuestCount() > 0) $completedTasks += 2;
        if ($weddingEvent->getStatus()->isConfirmed()) $completedTasks += 5;

        // Additional checks would go here for bookings, etc.
        
        $progressPercentage = ($completedTasks / $totalTasks) * 100;

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'progress_percentage' => round($progressPercentage, 1),
            'days_until_wedding' => $weddingEvent->daysUntilWedding(),
            'is_on_track' => $this->isWeddingOnTrack($weddingEvent, $progressPercentage)
        ];
    }

    private function isWeddingOnTrack(WeddingEvent $weddingEvent, float $progressPercentage): bool
    {
        $daysUntilWedding = $weddingEvent->daysUntilWedding();
        
        // Expected progress based on time remaining
        $expectedProgress = match (true) {
            $daysUntilWedding > 365 => 10,  // 10% if more than a year
            $daysUntilWedding > 180 => 30,  // 30% if more than 6 months
            $daysUntilWedding > 90 => 50,   // 50% if more than 3 months
            $daysUntilWedding > 30 => 75,   // 75% if more than 1 month
            default => 90                   // 90% if less than 1 month
        };

        return $progressPercentage >= $expectedProgress;
    }
}