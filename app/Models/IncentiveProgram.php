<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class IncentiveProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'period_type',
        'start_date',
        'end_date',
        'eligibility_criteria',
        'rewards',
        'max_winners',
        'is_active',
        'is_recurring',
        'recurrence_pattern',
        'status',
        'total_budget',
        'spent_budget',
        'participation_requirements',
        'tier_restrictions',
        'bonus_multipliers'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'eligibility_criteria' => 'array',
        'rewards' => 'array',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_pattern' => 'array',
        'total_budget' => 'decimal:2',
        'spent_budget' => 'decimal:2',
        'participation_requirements' => 'array',
        'tier_restrictions' => 'array',
        'bonus_multipliers' => 'array'
    ];

    protected $attributes = [
        'eligibility_criteria' => '[]',
        'rewards' => '[]',
        'recurrence_pattern' => '[]',
        'participation_requirements' => '[]',
        'tier_restrictions' => '[]',
        'bonus_multipliers' => '[]'
    ];

    // Relationships
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'incentive_participations')
                    ->withPivot(['participation_date', 'qualification_score', 'reward_earned', 'status'])
                    ->withTimestamps();
    }

    public function winners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'incentive_winners')
                    ->withPivot(['position', 'reward_type', 'reward_amount', 'awarded_at', 'status'])
                    ->withTimestamps();
    }

    public function raffleEntries(): HasMany
    {
        return $this->hasMany(RaffleEntry::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByPeriod(Builder $query, string $periodType): Builder
    {
        return $query->where('period_type', $periodType);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('end_date', '<', now())
                    ->where('status', 'completed');
    }

    // Business Logic Methods
    public function isEligible(User $user): bool
    {
        // Check if program is active and within date range
        if (!$this->is_active || !$this->isWithinDateRange()) {
            return false;
        }

        // Check tier restrictions
        if (!empty($this->tier_restrictions)) {
            $userTier = $user->currentTier?->name;
            if (!$userTier || !in_array($userTier, $this->tier_restrictions)) {
                return false;
            }
        }

        // Check participation requirements
        foreach ($this->participation_requirements as $requirement) {
            if (!$this->meetsRequirement($user, $requirement)) {
                return false;
            }
        }

        return true;
    }

    public function calculateQualificationScore(User $user): float
    {
        if (!$this->isEligible($user)) {
            return 0;
        }

        $score = 0;
        $periodStart = $this->start_date;
        $periodEnd = $this->end_date;

        foreach ($this->eligibility_criteria as $criterion) {
            $criterionScore = $this->calculateCriterionScore($user, $criterion, $periodStart, $periodEnd);
            
            // Apply tier multipliers if configured
            $tierMultiplier = $this->getTierMultiplier($user->currentTier?->name);
            $score += $criterionScore * $tierMultiplier;
        }

        return $score;
    }

    public function addParticipant(User $user): void
    {
        if (!$this->isEligible($user)) {
            throw new \Exception('User is not eligible for this incentive program.');
        }

        $qualificationScore = $this->calculateQualificationScore($user);

        $this->participants()->syncWithoutDetaching([
            $user->id => [
                'participation_date' => now(),
                'qualification_score' => $qualificationScore,
                'status' => 'active'
            ]
        ]);
    }

    public function processWinners(): array
    {
        if ($this->status === 'completed') {
            throw new \Exception('Program has already been completed.');
        }

        $participants = $this->participants()
            ->wherePivot('status', 'active')
            ->orderByPivot('qualification_score', 'desc')
            ->get();

        $winners = [];
        $position = 1;

        foreach ($this->rewards as $rewardConfig) {
            $winnersNeeded = $rewardConfig['quantity'] ?? 1;
            
            for ($i = 0; $i < $winnersNeeded && $participants->count() >= $position; $i++) {
                $winner = $participants->get($position - 1);
                
                $this->winners()->attach($winner->id, [
                    'position' => $position,
                    'reward_type' => $rewardConfig['type'],
                    'reward_amount' => $rewardConfig['amount'] ?? 0,
                    'awarded_at' => now(),
                    'status' => 'pending'
                ]);

                $winners[] = [
                    'user' => $winner,
                    'position' => $position,
                    'reward' => $rewardConfig
                ];

                $position++;
            }
        }

        $this->update(['status' => 'completed']);

        return $winners;
    }

    public function awardRewards(): array
    {
        $results = [];
        $winners = $this->winners()->wherePivot('status', 'pending')->get();

        foreach ($winners as $winner) {
            try {
                $rewardAmount = $winner->pivot->reward_amount;
                $rewardType = $winner->pivot->reward_type;

                if ($rewardType === 'monetary' && $rewardAmount > 0) {
                    Transaction::create([
                        'user_id' => $winner->id,
                        'amount' => $rewardAmount,
                        'transaction_type' => 'incentive_reward',
                        'status' => 'completed',
                        'description' => "Incentive program reward: {$this->name} - Position {$winner->pivot->position}",
                        'reference_number' => 'INC-' . $this->id . '-' . $winner->id . '-' . time(),
                        'processed_at' => now()
                    ]);

                    $winner->increment('total_earnings', $rewardAmount);
                    $this->increment('spent_budget', $rewardAmount);
                }

                $this->winners()->updateExistingPivot($winner->id, ['status' => 'awarded']);

                $results[] = [
                    'user_id' => $winner->id,
                    'user_name' => $winner->name,
                    'position' => $winner->pivot->position,
                    'reward_type' => $rewardType,
                    'reward_amount' => $rewardAmount,
                    'status' => 'success'
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'user_id' => $winner->id,
                    'user_name' => $winner->name,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return $results;
    }

    private function isWithinDateRange(): bool
    {
        $now = now();
        return $now->gte($this->start_date) && $now->lte($this->end_date);
    }

    private function meetsRequirement(User $user, array $requirement): bool
    {
        $type = $requirement['type'];
        $value = $requirement['value'];
        $operator = $requirement['operator'] ?? '>=';

        $currentValue = match ($type) {
            'active_subscription' => $user->hasActiveSubscription(),
            'referral_count' => $user->referral_count ?? 0,
            'tier_level' => $this->getTierLevel($user->currentTier?->name ?? 'Bronze'),
            'consecutive_months' => $user->getConsecutiveActiveMonths(),
            'course_completions' => $user->courseCompletions()->count(),
            default => 0
        };

        return match ($operator) {
            '>=' => $currentValue >= $value,
            '>' => $currentValue > $value,
            '=' => $currentValue == $value,
            '<=' => $currentValue <= $value,
            '<' => $currentValue < $value,
            default => false
        };
    }

    private function calculateCriterionScore(User $user, array $criterion, Carbon $start, Carbon $end): float
    {
        $type = $criterion['type'];
        $weight = $criterion['weight'] ?? 1.0;

        $score = match ($type) {
            'referrals' => $user->directReferrals()->whereBetween('created_at', [$start, $end])->count(),
            'team_volume' => $user->teamVolumes()->whereBetween('created_at', [$start, $end])->sum('team_volume'),
            'course_completions' => $user->courseCompletions()->whereBetween('completed_at', [$start, $end])->count(),
            'project_contributions' => $user->projectContributions()->whereBetween('contributed_at', [$start, $end])->sum('amount'),
            'achievement_points' => $user->userAchievements()->whereBetween('earned_at', [$start, $end])->with('achievement')->get()->sum('achievement.points'),
            default => 0
        };

        return $score * $weight;
    }

    private function getTierMultiplier(string $tierName): float
    {
        return $this->bonus_multipliers[$tierName] ?? 1.0;
    }

    private function getTierLevel(string $tierName): int
    {
        return match ($tierName) {
            'Bronze' => 1,
            'Silver' => 2,
            'Gold' => 3,
            'Diamond' => 4,
            'Elite' => 5,
            default => 0
        };
    }

    // Static methods for creating default programs
    public static function createDefaultPrograms(): array
    {
        $programs = [
            [
                'name' => 'Weekly Top Recruiters',
                'slug' => 'weekly-top-recruiters',
                'description' => 'Top 10 recruiters each week receive cash bonuses and gadgets',
                'type' => 'competition',
                'period_type' => 'weekly',
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 1.0]
                ],
                'rewards' => [
                    ['position' => 1, 'type' => 'monetary', 'amount' => 5000, 'quantity' => 1, 'description' => 'K5,000 + Smartphone'],
                    ['position' => 2, 'type' => 'monetary', 'amount' => 3000, 'quantity' => 1, 'description' => 'K3,000 + Tablet'],
                    ['position' => 3, 'type' => 'monetary', 'amount' => 2000, 'quantity' => 1, 'description' => 'K2,000 + Gadget'],
                    ['position' => '4-10', 'type' => 'monetary', 'amount' => 1000, 'quantity' => 7, 'description' => 'K1,000 bonus']
                ],
                'max_winners' => 10,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'weekly', 'day' => 'monday'],
                'total_budget' => 20000,
                'bonus_multipliers' => [
                    'Bronze' => 1.0,
                    'Silver' => 1.2,
                    'Gold' => 1.5,
                    'Diamond' => 1.8,
                    'Elite' => 2.0
                ]
            ],
            [
                'name' => 'Quarterly Raffle Extravaganza',
                'slug' => 'quarterly-raffle',
                'description' => 'Quarterly raffle for motorbikes, land plots, and smartphones',
                'type' => 'raffle',
                'period_type' => 'quarterly',
                'start_date' => now()->startOfQuarter(),
                'end_date' => now()->endOfQuarter(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 2.0],
                    ['type' => 'team_volume', 'weight' => 0.001], // 1 point per K1,000
                    ['type' => 'course_completions', 'weight' => 5.0]
                ],
                'rewards' => [
                    ['type' => 'physical', 'item' => 'motorbike', 'value' => 15000, 'quantity' => 3, 'description' => 'Motorbike'],
                    ['type' => 'physical', 'item' => 'land_plot', 'value' => 25000, 'quantity' => 2, 'description' => 'Land Plot'],
                    ['type' => 'physical', 'item' => 'smartphone', 'value' => 3000, 'quantity' => 10, 'description' => 'Smartphone']
                ],
                'max_winners' => 15,
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'quarterly'],
                'participation_requirements' => [
                    ['type' => 'active_subscription', 'value' => true],
                    ['type' => 'referral_count', 'operator' => '>=', 'value' => 1]
                ]
            ],
            [
                'name' => 'Profit Boost Week',
                'slug' => 'profit-boost-week',
                'description' => '25% commission rate increase for one week',
                'type' => 'bonus_multiplier',
                'period_type' => 'weekly',
                'start_date' => now()->addWeek()->startOfWeek(),
                'end_date' => now()->addWeek()->endOfWeek(),
                'eligibility_criteria' => [
                    ['type' => 'referrals', 'weight' => 1.0],
                    ['type' => 'team_volume', 'weight' => 0.001]
                ],
                'rewards' => [
                    ['type' => 'commission_boost', 'multiplier' => 1.25, 'description' => '25% commission increase']
                ],
                'is_recurring' => true,
                'recurrence_pattern' => ['frequency' => 'monthly', 'week' => 2],
                'participation_requirements' => [
                    ['type' => 'active_subscription', 'value' => true]
                ]
            ]
        ];

        $created = [];
        foreach ($programs as $programData) {
            $created[] = self::create($programData);
        }

        return $created;
    }
}