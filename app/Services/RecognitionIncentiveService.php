<?php

namespace App\Services;

use App\Models\IncentiveProgram;
use App\Models\RaffleEntry;
use App\Models\RecognitionEvent;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Achievement;
use App\Models\InvestmentTier;
use App\Services\GamificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecognitionIncentiveService
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * Process weekly incentive programs
     */
    public function processWeeklyIncentives(): array
    {
        $results = [];
        
        $weeklyPrograms = IncentiveProgram::active()
            ->byPeriod('weekly')
            ->where('end_date', '<=', now())
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($weeklyPrograms as $program) {
            try {
                $result = $this->processIncentiveProgram($program);
                $results[] = array_merge($result, [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'status' => 'success'
                ]);

                Log::info('Weekly incentive program processed', [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'winners_count' => count($result['winners'])
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to process weekly incentive program', [
                    'program_id' => $program->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Process quarterly raffle programs
     */
    public function processQuarterlyRaffles(): array
    {
        $results = [];
        
        $rafflePrograms = IncentiveProgram::active()
            ->byType('raffle')
            ->byPeriod('quarterly')
            ->where('end_date', '<=', now())
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($rafflePrograms as $program) {
            try {
                $result = $this->processRaffleProgram($program);
                $results[] = array_merge($result, [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'status' => 'success'
                ]);

                Log::info('Quarterly raffle processed', [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'total_entries' => $result['total_entries'],
                    'winners_count' => count($result['winners'])
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];

                Log::error('Failed to process quarterly raffle', [
                    'program_id' => $program->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Process a specific incentive program
     */
    public function processIncentiveProgram(IncentiveProgram $program): array
    {
        DB::beginTransaction();

        try {
            // Update participant scores
            $this->updateParticipantScores($program);

            // Process winners
            $winners = $program->processWinners();

            // Award rewards
            $rewardResults = $program->awardRewards();

            // Create certificates for winners if applicable
            $this->createWinnerCertificates($program, $winners);

            // Create next recurring program if applicable
            if ($program->is_recurring) {
                $this->createNextRecurringProgram($program);
            }

            DB::commit();

            return [
                'winners' => $winners,
                'reward_results' => $rewardResults,
                'total_participants' => $program->participants()->count(),
                'total_budget_spent' => $program->spent_budget
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process a raffle program with entry-based selection
     */
    public function processRaffleProgram(IncentiveProgram $program): array
    {
        DB::beginTransaction();

        try {
            // Update raffle entries for all eligible users
            $this->updateRaffleEntries($program);

            // Conduct raffle drawing
            $winners = $this->conductRaffleDrawing($program);

            // Award raffle prizes
            $rewardResults = $this->awardRafflePrizes($program, $winners);

            // Create certificates for winners
            $this->createRaffleWinnerCertificates($program, $winners);

            // Create next recurring raffle if applicable
            if ($program->is_recurring) {
                $this->createNextRecurringProgram($program);
            }

            $program->update(['status' => 'completed']);

            DB::commit();

            $totalEntries = $program->raffleEntries()->sum('entry_count');

            return [
                'winners' => $winners,
                'reward_results' => $rewardResults,
                'total_entries' => $totalEntries,
                'total_participants' => $program->raffleEntries()->count()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update participant qualification scores
     */
    private function updateParticipantScores(IncentiveProgram $program): void
    {
        $eligibleUsers = User::whereHas('currentTier')->get();

        foreach ($eligibleUsers as $user) {
            if ($program->isEligible($user)) {
                $program->addParticipant($user);
            }
        }
    }

    /**
     * Update raffle entries for all eligible users
     */
    private function updateRaffleEntries(IncentiveProgram $program): void
    {
        $eligibleUsers = User::whereHas('currentTier')->get();

        foreach ($eligibleUsers as $user) {
            if ($program->isEligible($user)) {
                $raffleEntry = RaffleEntry::firstOrCreate([
                    'incentive_program_id' => $program->id,
                    'user_id' => $user->id
                ]);

                $raffleEntry->updateEntryCount($user, $program);
            }
        }
    }

    /**
     * Conduct raffle drawing using weighted random selection
     */
    private function conductRaffleDrawing(IncentiveProgram $program): array
    {
        $entries = $program->raffleEntries()->with('user')->get();
        $winners = [];
        $availableRewards = $program->rewards;

        // Create weighted pool of entries
        $entryPool = [];
        foreach ($entries as $entry) {
            for ($i = 0; $i < $entry->entry_count; $i++) {
                $entryPool[] = $entry;
            }
        }

        // Shuffle the pool for randomness
        shuffle($entryPool);

        $position = 1;
        $usedUserIds = [];

        foreach ($availableRewards as $reward) {
            $quantity = $reward['quantity'] ?? 1;
            
            for ($i = 0; $i < $quantity; $i++) {
                if (empty($entryPool)) {
                    break;
                }

                // Select random entry from pool
                $randomIndex = array_rand($entryPool);
                $selectedEntry = $entryPool[$randomIndex];

                // Ensure user hasn't already won
                if (in_array($selectedEntry->user_id, $usedUserIds)) {
                    // Remove this entry and try again
                    unset($entryPool[$randomIndex]);
                    $entryPool = array_values($entryPool);
                    $i--; // Retry this iteration
                    continue;
                }

                // Mark as winner
                $selectedEntry->markAsWinner($position, $reward);
                $usedUserIds[] = $selectedEntry->user_id;

                $winners[] = [
                    'user' => $selectedEntry->user,
                    'position' => $position,
                    'reward' => $reward,
                    'entry_count' => $selectedEntry->entry_count
                ];

                // Remove all entries for this user from the pool
                $entryPool = array_filter($entryPool, function($entry) use ($selectedEntry) {
                    return $entry->user_id !== $selectedEntry->user_id;
                });
                $entryPool = array_values($entryPool);

                $position++;
            }
        }

        return $winners;
    }

    /**
     * Award raffle prizes to winners
     */
    private function awardRafflePrizes(IncentiveProgram $program, array $winners): array
    {
        $results = [];

        foreach ($winners as $winner) {
            try {
                $user = $winner['user'];
                $reward = $winner['reward'];

                if ($reward['type'] === 'physical') {
                    // Create physical reward allocation
                    $this->createPhysicalRewardAllocation($user, $reward);
                } elseif ($reward['type'] === 'monetary' && ($reward['value'] ?? 0) > 0) {
                    // Award monetary prize
                    Transaction::create([
                        'user_id' => $user->id,
                        'amount' => $reward['value'],
                        'transaction_type' => 'raffle_prize',
                        'status' => 'completed',
                        'description' => "Raffle prize: {$reward['description']} from {$program->name}",
                        'reference_number' => 'RAF-' . $program->id . '-' . $user->id . '-' . time(),
                        'processed_at' => now()
                    ]);

                    $user->increment('total_earnings', $reward['value']);
                }

                $results[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'position' => $winner['position'],
                    'reward_type' => $reward['type'],
                    'reward_description' => $reward['description'],
                    'status' => 'success'
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'user_id' => $winner['user']->id,
                    'user_name' => $winner['user']->name,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return $results;
    }

    /**
     * Create physical reward allocation for raffle winners
     */
    private function createPhysicalRewardAllocation(User $user, array $reward): void
    {
        // This would integrate with the existing physical reward system
        // For now, we'll create a placeholder allocation
        
        Log::info('Physical reward allocated', [
            'user_id' => $user->id,
            'reward_type' => $reward['item'],
            'reward_value' => $reward['value'],
            'description' => $reward['description']
        ]);
    }

    /**
     * Create certificates for program winners
     */
    private function createWinnerCertificates(IncentiveProgram $program, array $winners): void
    {
        foreach ($winners as $winner) {
            Certificate::create([
                'user_id' => $winner['user']->id,
                'certificate_type' => 'recognition_award',
                'title' => "Winner Certificate: {$program->name}",
                'description' => "Awarded for achieving position {$winner['position']} in {$program->name}",
                'issued_date' => now(),
                'template_data' => [
                    'recipient_name' => $winner['user']->name,
                    'program_name' => $program->name,
                    'position' => $winner['position'],
                    'reward_description' => $winner['reward']['description'] ?? '',
                    'achievement_date' => now()->format('F j, Y')
                ],
                'is_digital' => true,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Create certificates for raffle winners
     */
    private function createRaffleWinnerCertificates(IncentiveProgram $program, array $winners): void
    {
        foreach ($winners as $winner) {
            Certificate::create([
                'user_id' => $winner['user']->id,
                'certificate_type' => 'recognition_award',
                'title' => "Raffle Winner Certificate: {$program->name}",
                'description' => "Winner of {$winner['reward']['description']} in {$program->name}",
                'issued_date' => now(),
                'template_data' => [
                    'recipient_name' => $winner['user']->name,
                    'program_name' => $program->name,
                    'prize_description' => $winner['reward']['description'],
                    'entry_count' => $winner['entry_count'],
                    'winning_date' => now()->format('F j, Y')
                ],
                'is_digital' => true,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Create next recurring program
     */
    private function createNextRecurringProgram(IncentiveProgram $program): void
    {
        if (!$program->is_recurring || empty($program->recurrence_pattern)) {
            return;
        }

        $pattern = $program->recurrence_pattern;
        $frequency = $pattern['frequency'];

        $nextStart = match ($frequency) {
            'weekly' => $program->end_date->addWeek()->startOfWeek(),
            'monthly' => $program->end_date->addMonth()->startOfMonth(),
            'quarterly' => $program->end_date->addQuarter()->startOfQuarter(),
            default => $program->end_date->addWeek()
        };

        $nextEnd = match ($frequency) {
            'weekly' => $nextStart->copy()->endOfWeek(),
            'monthly' => $nextStart->copy()->endOfMonth(),
            'quarterly' => $nextStart->copy()->endOfQuarter(),
            default => $nextStart->copy()->addWeek()
        };

        IncentiveProgram::create([
            'name' => $program->name,
            'slug' => $program->slug . '-' . $nextStart->format('Y-m-d'),
            'description' => $program->description,
            'type' => $program->type,
            'period_type' => $program->period_type,
            'start_date' => $nextStart,
            'end_date' => $nextEnd,
            'eligibility_criteria' => $program->eligibility_criteria,
            'rewards' => $program->rewards,
            'max_winners' => $program->max_winners,
            'is_active' => true,
            'is_recurring' => true,
            'recurrence_pattern' => $program->recurrence_pattern,
            'status' => 'active',
            'total_budget' => $program->total_budget,
            'participation_requirements' => $program->participation_requirements,
            'tier_restrictions' => $program->tier_restrictions,
            'bonus_multipliers' => $program->bonus_multipliers
        ]);
    }

    /**
     * Get user's incentive program participation summary
     */
    public function getUserIncentiveSummary(User $user): array
    {
        return [
            'active_programs' => $this->getUserActivePrograms($user),
            'program_history' => $this->getUserProgramHistory($user),
            'raffle_entries' => $this->getUserRaffleEntries($user),
            'certificates_earned' => $this->getUserCertificates($user),
            'total_incentive_earnings' => $this->getUserIncentiveEarnings($user),
            'upcoming_events' => $this->getUserUpcomingEvents($user)
        ];
    }

    private function getUserActivePrograms(User $user): array
    {
        return IncentiveProgram::active()
            ->current()
            ->get()
            ->filter(function ($program) use ($user) {
                return $program->isEligible($user);
            })
            ->map(function ($program) use ($user) {
                return [
                    'program_id' => $program->id,
                    'name' => $program->name,
                    'type' => $program->type,
                    'end_date' => $program->end_date,
                    'qualification_score' => $program->calculateQualificationScore($user),
                    'current_position' => $this->getUserCurrentPosition($user, $program)
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUserProgramHistory(User $user): array
    {
        return $user->incentivePrograms()
            ->wherePivot('status', 'completed')
            ->orderBy('pivot_participation_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($program) {
                return [
                    'program_name' => $program->name,
                    'participation_date' => $program->pivot->participation_date,
                    'qualification_score' => $program->pivot->qualification_score,
                    'reward_earned' => $program->pivot->reward_earned
                ];
            })
            ->toArray();
    }

    private function getUserRaffleEntries(User $user): array
    {
        return RaffleEntry::forUser($user)
            ->with('incentiveProgram')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($entry) {
                return $entry->getEntrySummary();
            })
            ->toArray();
    }

    private function getUserCertificates(User $user): array
    {
        return Certificate::forUser($user)
            ->orderBy('issued_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($certificate) {
                return [
                    'certificate_number' => $certificate->certificate_number,
                    'title' => $certificate->title,
                    'type' => $certificate->certificate_type,
                    'issued_date' => $certificate->issued_date,
                    'verification_url' => $certificate->getPublicVerificationUrl()
                ];
            })
            ->toArray();
    }

    private function getUserIncentiveEarnings(User $user): float
    {
        return Transaction::where('user_id', $user->id)
            ->whereIn('transaction_type', ['incentive_reward', 'raffle_prize', 'recognition_award'])
            ->sum('amount');
    }

    private function getUserUpcomingEvents(User $user): array
    {
        return RecognitionEvent::upcoming()
            ->registrationOpen()
            ->get()
            ->filter(function ($event) use ($user) {
                return $event->isEligible($user);
            })
            ->map(function ($event) {
                return [
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'event_date' => $event->event_date,
                    'location' => $event->location,
                    'is_virtual' => $event->is_virtual,
                    'registration_deadline' => $event->registration_deadline
                ];
            })
            ->values()
            ->toArray();
    }

    private function getUserCurrentPosition(User $user, IncentiveProgram $program): ?int
    {
        $participants = $program->participants()
            ->orderByPivot('qualification_score', 'desc')
            ->get();

        $position = 1;
        foreach ($participants as $participant) {
            if ($participant->id === $user->id) {
                return $position;
            }
            $position++;
        }

        return null;
    }

    /**
     * Trigger achievement-based recognition
     */
    public function triggerAchievementRecognition(User $user, Achievement $achievement): void
    {
        // Create certificate for significant achievements
        if ($achievement->triggers_celebration) {
            Certificate::createAchievementCertificate($user, $achievement);
        }

        // Check for tier advancement recognition
        if ($achievement->category === 'tier_advancement') {
            $this->triggerTierAdvancementRecognition($user);
        }
    }

    /**
     * Trigger tier advancement recognition
     */
    public function triggerTierAdvancementRecognition(User $user): void
    {
        $currentTier = $user->currentTier;
        if ($currentTier) {
            Certificate::createTierAdvancementCertificate($user, $currentTier);
        }
    }
}