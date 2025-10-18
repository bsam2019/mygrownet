<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ProjectVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'community_project_id',
        'vote_type',
        'vote_subject',
        'vote_description',
        'vote',
        'voting_power',
        'tier_at_vote',
        'contribution_amount',
        'vote_options',
        'voter_comments',
        'voted_at',
        'vote_session_id',
        'vote_deadline',
        'is_final_vote'
    ];

    protected $casts = [
        'voting_power' => 'decimal:4',
        'contribution_amount' => 'decimal:2',
        'vote_options' => 'array',
        'voted_at' => 'datetime',
        'vote_deadline' => 'date',
        'is_final_vote' => 'boolean'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(CommunityProject::class, 'community_project_id');
    }

    // Scopes
    public function scopeForProject(Builder $query, CommunityProject $project): Builder
    {
        return $query->where('community_project_id', $project->id);
    }

    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('vote_session_id', $sessionId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('vote_type', $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('vote_deadline', '>=', now());
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('vote_deadline', '<', now());
    }

    // Business Logic Methods
    public static function createVoteSession(
        CommunityProject $project,
        string $voteType,
        string $subject,
        string $description = null,
        array $options = null,
        int $durationDays = 7
    ): string {
        $sessionId = 'VOTE-' . $project->id . '-' . time() . '-' . uniqid();
        $deadline = now()->addDays($durationDays)->toDateString();

        // Store vote session metadata (you might want a separate table for this)
        // For now, we'll use the first vote to store session info
        
        return $sessionId;
    }

    public static function getVoteSessionResults(string $sessionId): array
    {
        $votes = self::forSession($sessionId)->get();
        
        if ($votes->isEmpty()) {
            return [
                'session_id' => $sessionId,
                'total_votes' => 0,
                'results' => [],
                'status' => 'no_votes'
            ];
        }

        $totalVotingPower = $votes->sum('voting_power');
        $results = $votes->groupBy('vote')->map(function ($voteGroup) use ($totalVotingPower) {
            $votePower = $voteGroup->sum('voting_power');
            return [
                'count' => $voteGroup->count(),
                'voting_power' => $votePower,
                'percentage' => $totalVotingPower > 0 ? ($votePower / $totalVotingPower) * 100 : 0
            ];
        });

        $firstVote = $votes->first();
        $isActive = now()->lte($firstVote->vote_deadline);
        
        return [
            'session_id' => $sessionId,
            'project_id' => $firstVote->community_project_id,
            'vote_type' => $firstVote->vote_type,
            'vote_subject' => $firstVote->vote_subject,
            'vote_deadline' => $firstVote->vote_deadline,
            'is_active' => $isActive,
            'total_votes' => $votes->count(),
            'total_voting_power' => $totalVotingPower,
            'results' => $results,
            'status' => $isActive ? 'active' : 'completed',
            'winning_option' => $results->sortByDesc('voting_power')->keys()->first()
        ];
    }

    public function canUserVote(User $user, CommunityProject $project): bool
    {
        // Check if user has already voted in this session
        $existingVote = self::where('user_id', $user->id)
            ->where('vote_session_id', $this->vote_session_id)
            ->exists();

        if ($existingVote) {
            return false;
        }

        // Check if vote is still active
        if (now()->gt($this->vote_deadline)) {
            return false;
        }

        // Check if user has contribution to the project
        $contribution = $project->getUserContributionTotal($user);
        if ($contribution <= 0) {
            return false;
        }

        // Check tier access
        return $project->isAccessibleByUser($user);
    }

    public static function castVote(
        User $user,
        CommunityProject $project,
        string $sessionId,
        string $vote,
        string $comments = null
    ): self {
        // Validate vote session exists and is active
        $existingVote = self::forSession($sessionId)->first();
        if (!$existingVote) {
            throw new \Exception('Invalid vote session.');
        }

        if (now()->gt($existingVote->vote_deadline)) {
            throw new \Exception('Voting period has ended.');
        }

        // Check if user already voted
        $userVote = self::where('user_id', $user->id)
            ->where('vote_session_id', $sessionId)
            ->first();

        if ($userVote) {
            throw new \Exception('User has already voted in this session.');
        }

        // Calculate user's voting power
        $contribution = $project->getUserContributionTotal($user);
        if ($contribution <= 0) {
            throw new \Exception('User must have a contribution to vote.');
        }

        $votingPower = $project->getUserVotingPower($user);
        $tierName = $user->currentTier?->name ?? 'Bronze';

        return self::create([
            'user_id' => $user->id,
            'community_project_id' => $project->id,
            'vote_type' => $existingVote->vote_type,
            'vote_subject' => $existingVote->vote_subject,
            'vote_description' => $existingVote->vote_description,
            'vote' => $vote,
            'voting_power' => $votingPower,
            'tier_at_vote' => $tierName,
            'contribution_amount' => $contribution,
            'voter_comments' => $comments,
            'voted_at' => now(),
            'vote_session_id' => $sessionId,
            'vote_deadline' => $existingVote->vote_deadline
        ]);
    }

    public static function getVoteTypes(): array
    {
        return [
            'approval' => 'Project Approval',
            'milestone' => 'Milestone Approval',
            'change_request' => 'Project Change Request',
            'termination' => 'Project Termination',
            'manager_change' => 'Project Manager Change'
        ];
    }

    public function getVoteWeight(): float
    {
        return $this->voting_power;
    }

    public function getVoteSummary(): array
    {
        return [
            'vote_id' => $this->id,
            'project_name' => $this->project->name,
            'vote_type' => $this->vote_type,
            'vote_subject' => $this->vote_subject,
            'user_vote' => $this->vote,
            'voting_power' => $this->voting_power,
            'tier_at_vote' => $this->tier_at_vote,
            'contribution_amount' => $this->contribution_amount,
            'voted_at' => $this->voted_at,
            'vote_deadline' => $this->vote_deadline,
            'comments' => $this->voter_comments
        ];
    }
}