<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InvestmentOpportunityVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'investment_opportunity_id',
        'user_id',
        'investment_id',
        'vote_type',
        'voting_power',
        'tier_at_vote',
        'investment_amount_at_vote',
        'vote_reason',
        'vote_criteria_scores',
        'comments',
        'voted_at',
        'voter_ip',
        'voter_user_agent'
    ];

    protected $casts = [
        'voting_power' => 'decimal:4',
        'investment_amount_at_vote' => 'decimal:2',
        'vote_criteria_scores' => 'array',
        'voted_at' => 'datetime'
    ];

    protected $attributes = [
        'vote_criteria_scores' => '[]'
    ];

    // Relationships
    public function investmentOpportunity(): BelongsTo
    {
        return $this->belongsTo(InvestmentOpportunity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    // Scopes
    public function scopeApprove(Builder $query): Builder
    {
        return $query->where('vote_type', 'approve');
    }

    public function scopeReject(Builder $query): Builder
    {
        return $query->where('vote_type', 'reject');
    }

    public function scopeAbstain(Builder $query): Builder
    {
        return $query->where('vote_type', 'abstain');
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier_at_vote', $tier);
    }

    public function scopeWithHighVotingPower(Builder $query, float $minimumPower = 1.0): Builder
    {
        return $query->where('voting_power', '>=', $minimumPower);
    }

    // Business Logic Methods
    public function isApprovalVote(): bool
    {
        return $this->vote_type === 'approve';
    }

    public function isRejectionVote(): bool
    {
        return $this->vote_type === 'reject';
    }

    public function isAbstention(): bool
    {
        return $this->vote_type === 'abstain';
    }

    public function getVoteWeight(): float
    {
        return $this->voting_power;
    }

    public function hasComments(): bool
    {
        return !empty($this->comments);
    }

    public function hasReason(): bool
    {
        return !empty($this->vote_reason);
    }

    public function hasCriteriaScores(): bool
    {
        return !empty($this->vote_criteria_scores);
    }

    public function getAverageCriteriaScore(): float
    {
        if (empty($this->vote_criteria_scores)) {
            return 0;
        }

        $scores = array_values($this->vote_criteria_scores);
        return array_sum($scores) / count($scores);
    }

    public function getCriteriaScore(string $criterion): ?float
    {
        return $this->vote_criteria_scores[$criterion] ?? null;
    }

    public function getVoteDetails(): array
    {
        return [
            'vote_id' => $this->id,
            'voter' => [
                'user_id' => $this->user_id,
                'name' => $this->user->name ?? 'Unknown',
                'tier_at_vote' => $this->tier_at_vote
            ],
            'vote_details' => [
                'type' => $this->vote_type,
                'voting_power' => $this->voting_power,
                'investment_amount' => $this->investment_amount_at_vote,
                'voted_at' => $this->voted_at
            ],
            'reasoning' => [
                'reason' => $this->vote_reason,
                'comments' => $this->comments,
                'criteria_scores' => $this->vote_criteria_scores,
                'average_score' => $this->getAverageCriteriaScore()
            ]
        ];
    }

    public static function castVote(
        InvestmentOpportunity $opportunity,
        User $user,
        string $voteType,
        ?string $reason = null,
        ?string $comments = null,
        ?array $criteriaScores = null
    ): self {
        if (!$opportunity->canUserVote($user)) {
            throw new \Exception('User is not eligible to vote on this opportunity.');
        }

        $investment = $opportunity->investments()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $votingPower = $opportunity->getUserVotingPower($user);

        $vote = self::create([
            'investment_opportunity_id' => $opportunity->id,
            'user_id' => $user->id,
            'investment_id' => $investment?->id,
            'vote_type' => $voteType,
            'voting_power' => $votingPower,
            'tier_at_vote' => $user->currentTier?->name ?? 'Bronze',
            'investment_amount_at_vote' => $investment?->amount ?? 0,
            'vote_reason' => $reason,
            'comments' => $comments,
            'vote_criteria_scores' => $criteriaScores ?? [],
            'voted_at' => now(),
            'voter_ip' => request()->ip(),
            'voter_user_agent' => request()->userAgent()
        ]);

        // Update investment to mark voting participation
        if ($investment) {
            $investment->update(['participated_in_voting' => true]);
        }

        // Update opportunity vote count
        $opportunity->increment('total_community_votes');

        return $vote;
    }

    public function getVotingSummary(): array
    {
        return [
            'vote_type' => $this->vote_type,
            'voting_power' => $this->voting_power,
            'tier' => $this->tier_at_vote,
            'investment_amount' => $this->investment_amount_at_vote,
            'has_reason' => $this->hasReason(),
            'has_comments' => $this->hasComments(),
            'has_criteria_scores' => $this->hasCriteriaScores(),
            'average_criteria_score' => $this->getAverageCriteriaScore(),
            'voted_at' => $this->voted_at
        ];
    }
}