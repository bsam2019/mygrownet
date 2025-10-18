<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RaffleEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'incentive_program_id',
        'user_id',
        'entry_count',
        'qualification_score',
        'entry_source',
        'bonus_multiplier',
        'is_winner',
        'winning_position',
        'reward_details'
    ];

    protected $casts = [
        'qualification_score' => 'decimal:2',
        'bonus_multiplier' => 'decimal:2',
        'is_winner' => 'boolean',
        'entry_source' => 'array',
        'reward_details' => 'array'
    ];

    protected $attributes = [
        'entry_source' => '[]',
        'reward_details' => '[]'
    ];

    // Relationships
    public function incentiveProgram(): BelongsTo
    {
        return $this->belongsTo(IncentiveProgram::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeForProgram(Builder $query, IncentiveProgram $program): Builder
    {
        return $query->where('incentive_program_id', $program->id);
    }

    public function scopeWinners(Builder $query): Builder
    {
        return $query->where('is_winner', true);
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->whereHas('user', function ($q) use ($tier) {
            $q->whereHas('currentTier', function ($tq) use ($tier) {
                $tq->where('name', $tier);
            });
        });
    }

    // Business Logic Methods
    public function calculateEntries(User $user, IncentiveProgram $program): int
    {
        $baseEntries = 1; // Everyone gets at least 1 entry
        $bonusEntries = 0;

        // Calculate bonus entries based on activity
        $periodStart = $program->start_date;
        $periodEnd = $program->end_date;

        // Referral entries (1 entry per referral)
        $referralCount = $user->directReferrals()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();
        $bonusEntries += $referralCount;

        // Subscription consistency entries (1 entry per consecutive month)
        $consecutiveMonths = $user->getConsecutiveActiveMonths();
        $bonusEntries += min($consecutiveMonths, 12); // Cap at 12 months

        // Course completion entries (2 entries per course)
        $courseCompletions = $user->courseCompletions()
            ->whereBetween('completed_at', [$periodStart, $periodEnd])
            ->count();
        $bonusEntries += $courseCompletions * 2;

        // Team volume entries (1 entry per K5,000 team volume)
        $teamVolume = $user->teamVolumes()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('team_volume');
        $bonusEntries += floor($teamVolume / 5000);

        // Apply tier multiplier
        $tierMultiplier = $this->getTierMultiplier($user->currentTier?->name);
        $totalEntries = ($baseEntries + $bonusEntries) * $tierMultiplier;

        return (int) $totalEntries;
    }

    public function updateEntryCount(User $user, IncentiveProgram $program): void
    {
        $entryCount = $this->calculateEntries($user, $program);
        $qualificationScore = $program->calculateQualificationScore($user);
        $tierMultiplier = $this->getTierMultiplier($user->currentTier?->name);

        $this->update([
            'entry_count' => $entryCount,
            'qualification_score' => $qualificationScore,
            'bonus_multiplier' => $tierMultiplier,
            'entry_source' => $this->getEntrySourceBreakdown($user, $program)
        ]);
    }

    public function markAsWinner(int $position, array $rewardDetails): void
    {
        $this->update([
            'is_winner' => true,
            'winning_position' => $position,
            'reward_details' => $rewardDetails
        ]);
    }

    private function getTierMultiplier(string $tierName): float
    {
        return match ($tierName) {
            'Bronze' => 1.0,
            'Silver' => 1.2,
            'Gold' => 1.5,
            'Diamond' => 1.8,
            'Elite' => 2.0,
            default => 1.0
        };
    }

    private function getEntrySourceBreakdown(User $user, IncentiveProgram $program): array
    {
        $periodStart = $program->start_date;
        $periodEnd = $program->end_date;

        return [
            'base_entries' => 1,
            'referral_entries' => $user->directReferrals()->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
            'subscription_entries' => min($user->getConsecutiveActiveMonths(), 12),
            'course_entries' => $user->courseCompletions()->whereBetween('completed_at', [$periodStart, $periodEnd])->count() * 2,
            'volume_entries' => floor($user->teamVolumes()->whereBetween('created_at', [$periodStart, $periodEnd])->sum('team_volume') / 5000),
            'tier_multiplier' => $this->getTierMultiplier($user->currentTier?->name)
        ];
    }

    public function getEntrySummary(): array
    {
        return [
            'user_name' => $this->user->name,
            'user_tier' => $this->user->currentTier?->name ?? 'Bronze',
            'entry_count' => $this->entry_count,
            'qualification_score' => $this->qualification_score,
            'bonus_multiplier' => $this->bonus_multiplier,
            'entry_breakdown' => $this->entry_source,
            'is_winner' => $this->is_winner,
            'winning_position' => $this->winning_position,
            'reward_details' => $this->reward_details
        ];
    }
}